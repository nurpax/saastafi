
module P = Printf

module SMap = Map.Make (String)

type vote = 
    {
      v_top5 : string array;
      v_hb : string option;
    }

type post_info = 
    {
      pi_post_id : int;
      pi_author : string;
      pi_title : string;
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

let url_re_s = "http://(www.)?saasta.fi/(.*)p=([0-9]+)" 

let url_re = Pcre.regexp ("^"^url_re_s^"$")

let voteline_re = Pcre.regexp ("^([0-9]). "^url_re_s^"[ \n\r]*$")

let homebrew_re = Pcre.regexp ("^homebrew: "^url_re_s^"[ \n\r]*$")

let read_vote_files () =

  let vote_dir_name = "data/votes_q123_2008" in
  let parse_vote dst ndx s =
    Printf.printf "voteline '%s'\n" s;
    let m = (Pcre.extract ~rex:voteline_re s) in
    let rank = m.(1) in
    let url = m.(4) in
    assert (int_of_string rank = ndx+1);
    dst.(ndx) <- url in

  let parse_homebrew s =
    try
      let m = (Pcre.extract ~rex:homebrew_re s) in
      Some m.(3)
    with 
      Not_found ->
        None in
    
  let read_votes fname =
    let votes = Array.make 5 "" in
    with_open_in fname
      (fun inchnl ->
         try
           for i = 0 to 4 do
             parse_vote votes i (input_line inchnl);
           done;
           let hb = 
             parse_homebrew (input_line inchnl) in
           { 
             v_top5 = votes;
             v_hb = hb;
           }
         with
           End_of_file ->
             error (P.sprintf "premature eof when reading %s\n" fname)) in

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
    ~url:"http://www.saasta.fi/saasta/xmlrpc.php"
    ~blog_id:1
    ~username:"admin"
    ~password:(Sys.getenv "ADMIN_PASSWD")

module WPPost = WordPress.Post

let datetime_year (y,_,_,_,_,_,_) = y
let datetime_month (_,m,_,_,_,_,_) = m

(* Query post title & poster via xmlrpc.php directly from the site *)
let query_post_info post_id_s =
  let post_id = int_of_string post_id_s in
  flush_all ();
  let p = wp#get_post post_id in
  let p_year = datetime_year p.WPPost.date_created in
  let p_month = datetime_month p.WPPost.date_created in
  { 
    pi_post_id = post_id;
    pi_author = p.WPPost.wp_author_display_name;
    pi_title = p.WPPost.title;
    pi_year = p_year;
    pi_month = p_month
  }

(* Business logic for Q3/2007 *)
let check_vote_validity pi = 
  if pi.pi_post_id = 0 then
    ()
  else 
    if not ((List.mem pi.pi_month [1;2;3;4;5;6;7;8;9;10]) && pi.pi_year = 2008) then
      error (P.sprintf "post '%i' not created in Q3/2007! month %i year %i" pi.pi_post_id pi.pi_month pi.pi_year)
    else
      ()

let sort_results m =
  let comp (an,_) (bn,_) = compare bn an  in
  List.sort comp (SMap.fold (fun k v acc -> (v,k)::acc) m [])

let compute_best_posts votes =
  let (post_histogram, post_scores, poster_score) =
    List.fold_left
      (fun (post_histo, post_score, poster_score) vote ->
         arr_fold_lefti
           (fun ndx (post_histo, post_score, poster_score) post ->
              if post <> "0" then
                begin
                  let hist = default_find SMap.find post post_histo 0 in
                  let old_score = default_find SMap.find post post_score 0 in
                  (* Skip empty entries in Top 5's *)
                  P.printf "%s\n" post;
                  let post_info = 
                    query_post_info post in
                  P.printf "%s\n" post_info.pi_title;
                  flush_all ();
                  check_vote_validity post_info;
                  let poster = post_info.pi_author in
                  let old_poster_score = 
                    default_find SMap.find poster poster_score 0 in
                  let score = rank_score ndx in
                  let post_histo' = SMap.add post (hist+1) post_histo in
                  let post_score' = SMap.add post (old_score+score) post_score in
                  let poster_score' = 
                    SMap.add poster (old_poster_score+score) poster_score in
                  (post_histo', post_score', poster_score')
                end
              else
                (post_histo, post_score, poster_score))
           (post_histo, post_score, poster_score) vote.v_top5)
      (SMap.empty, SMap.empty, SMap.empty) votes in
  (sort_results post_histogram, 
   sort_results post_scores, 
   sort_results poster_score)

let compute_homebrew_score votes =
  let hb_post_histo =
    List.fold_left
      (fun post_histo vote ->
         match vote.v_hb with
           None -> post_histo
         | Some post ->
             P.printf "hb '%s'\n" post;
             let post_info = 
               query_post_info post in
             check_vote_validity post_info;
             let hist = default_find SMap.find post post_histo 0 in
             SMap.add post (hist+1) post_histo)
      SMap.empty votes in
  sort_results hb_post_histo
         

let _ =
  let votes = read_vote_files () in
  let (post_counts,post_scores,poster_scores) = compute_best_posts votes in
  let hb_top = compute_homebrew_score votes in
  P.printf "counts\n";
  List.iter
    (fun (n_posts,post) ->
       Printf.printf "%s - %i\n" post n_posts) post_counts;
  P.printf "\nscores\n";
  List.iter
    (fun (score,post) ->
       let pi = query_post_info post in
       Printf.printf "%s\t%s\t%s\t%i\n" post pi.pi_title pi.pi_author score) post_scores;
  P.printf "\nposter scores\n";
  List.iter
    (fun (score,poster) ->
       Printf.printf "%s\t%i\n" poster score) poster_scores;
  P.printf "\nhomebrew top\n";
  List.iter
    (fun (score,post) ->
       let pi = query_post_info post in
       Printf.printf "%s\t%s\t%s\t%i\n" post pi.pi_title pi.pi_author score) 
    hb_top

