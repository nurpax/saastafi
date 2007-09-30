
module P = Printf

module SMap = Map.Make (String)

type vote = 
    {
      v_top5 : string array;
      v_hb : string;
    }

type post_info = 
    {
      pi_author : string;
      pi_url : string;
      pi_month : int;
      pi_year : int;
    }

let error msg = 
  P.eprintf "%s\n" msg;
  exit 1

let fold_read_lines f accum inchnl = 
  let line () = 
    try Some (input_line inchnl)
    with End_of_file -> None in
  let rec loop accum =
    match line () with
    | Some e -> loop (f accum e)
    | None -> accum in
  loop accum 

let with_open_in fname f = 
  let inchnl = open_in fname in
  Std.finally 
    (fun () -> close_in inchnl)
    (fun chnl -> f chnl) inchnl

let voteline_re = Pcre.regexp "^([0-9]). (.*)[ \n\r]*$"
let homebrew_re = Pcre.regexp "^homebrew:[ \t]*(.*)[ \n\r]*$"

let read_vote_files () =

  let vote_dir_name = "data/votes_q3_test" in
  let parse_vote dst ndx s =
    let m = (Pcre.extract ~rex:voteline_re s) in
    let rank = m.(1) in
    let url = m.(2) in
    assert (int_of_string rank = ndx+1);
    dst.(ndx) <- url in

  let parse_homebrew s =
    let m = (Pcre.extract ~rex:homebrew_re s) in
    m.(1) in
    
  let read_votes fname =
    let votes = Array.make 5 "" in
    with_open_in fname
      (fun inchnl ->
         for i = 0 to 4 do
           parse_vote votes i (input_line inchnl);
         done;
         let hb = 
           try 
             parse_homebrew (input_line inchnl)
           with
             End_of_file ->
               error (P.sprintf "homebrew missing from %s\n" fname) in
         { 
           v_top5 = votes;
           v_hb = hb;
         }) in


  let dir = Unix.opendir vote_dir_name in
  let rec loop acc =
    try 
      let fname = Unix.readdir dir in
      if fname <> "." && fname <> ".." && fname <> ".svn" then
        loop (read_votes (vote_dir_name^"/"^fname)::acc)
      else 
        loop acc
    with
      End_of_file ->
        acc in
  loop []

let default_find f k m d =
  try f k m with Not_found -> d

let arr_fold_lefti f acc arr = 
  let zipped = Array.mapi (fun ndx e -> (ndx,e)) arr in
  Array.fold_left  (fun acc (ndx,e) -> f ndx acc e) acc zipped
    
let rank_score = function
    0 -> 5
  | 1 -> 4
  | 2 -> 3
  | 3 -> 2
  | 4 -> 1
  | _ -> assert false

let wp =
  new WordPress.api
    ~url:"http://127.0.0.1:8000/saasta/xmlrpc.php"
    ~blog_id:1
    ~username:"admin"
    ~password:(Sys.getenv "ADMIN_PASSWD")

module WPPost = WordPress.Post

let url_re = Pcre.regexp "^http://(www.)?saasta.fi/(.*)p=([0-9]+)$"

let datetime_year (y,_,_,_,_,_,_) = y
let datetime_month (_,m,_,_,_,_,_) = m

(* Query post title & poster via xmlrpc.php directly from the site *)
let query_post_info post =
  let m = (Pcre.extract ~rex:url_re post) in
  let post_id = int_of_string m.(3) in
  P.printf "query post #%-5i" post_id;
  flush_all ();
  let p = wp#get_post post_id in
  let p_year = datetime_year p.WPPost.date_created in
  let p_month = datetime_month p.WPPost.date_created in
  P.printf " author: %-10s post title: '%s', year %i month %i\n" 
    p.WPPost.wp_author_display_name p.WPPost.title p_year p_month;
  { 
    pi_author = p.WPPost.wp_author_display_name;
    pi_url = post;
    pi_year = p_year;
    pi_month = p_month
  }

(* Business logic for Q3/2007 *)
let check_vote_validity pi = 
  if not (List.mem pi.pi_month [7; 8; 9]) then
    error (P.sprintf "post '%s' not created in Q3/2007!" pi.pi_url)
  else
    ()

let compute_best_posts votes =
  let comp (an,_) (bn,_) = compare bn an  in
  let sort_results m =
    List.sort comp (SMap.fold (fun k v acc -> (v,k)::acc) m []) in
  let (post_histogram, post_scores, poster_score) =
    List.fold_left
      (fun (post_histo, post_score, poster_score) vote ->
         arr_fold_lefti
           (fun ndx (post_histo, post_score, poster_score) post ->
              let hist = default_find SMap.find post post_histo 0 in
              let old_score = default_find SMap.find post post_score 0 in
              let post_info = 
                query_post_info post in
              check_vote_validity post_info;
              let poster = post_info.pi_author in
              let old_poster_score = 
                default_find SMap.find poster poster_score 0 in
              let score = rank_score ndx in
              let post_histo' = SMap.add post (hist+1) post_histo in
              let post_score' = SMap.add post (old_score+score) post_score in
              let poster_score' = 
                SMap.add poster (old_poster_score+score) poster_score in
              (post_histo', post_score', poster_score')) 
           (post_histo, post_score, poster_score) vote.v_top5)
      (SMap.empty, SMap.empty, SMap.empty) votes in
  (sort_results post_histogram, 
   sort_results post_scores, 
   sort_results poster_score)

let _ =
  let votes = read_vote_files () in
  let (post_counts,post_scores,poster_scores) = compute_best_posts votes in
  P.printf "counts\n";
  List.iter
    (fun (n_posts,post) ->
       Printf.printf "%s - %i\n" post n_posts) post_counts;
  P.printf "\nscores\n";
  List.iter
    (fun (score,post) ->
       Printf.printf "%s - %i\n" post score) post_scores;
  P.printf "\nposter scores\n";
  List.iter
    (fun (score,poster) ->
       Printf.printf "%s - %i\n" poster score) poster_scores
