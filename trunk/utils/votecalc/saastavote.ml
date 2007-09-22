
module P = Printf

module SMap = Map.Make (String)

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

let read_vote_files () =

  let vote_dir_name = "data/votes_q2_2007" in
  let parse_vote dst ndx s =
    let m = (Pcre.extract ~rex:voteline_re s) in
    let rank = m.(1) in
    let url = m.(2) in
    assert (int_of_string rank = ndx+1);
    dst.(ndx) <- url in
    
  let read_votes fname =
    let votes = Array.make 5 "" in
    let _ =
      with_open_in fname
        (fun inchnl ->
           fold_read_lines 
             (fun ndx line ->
                parse_vote votes ndx line;
                ndx+1) 0 inchnl) in
    votes in

  let dir = Unix.opendir vote_dir_name in
  let rec loop acc =
    try 
      let fname = Unix.readdir dir in
      if fname <> "." && fname <> ".." then
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
    ~password:(Sys.getenv "SAASTA_ADMIN_PASSWD")

module WPPost = WordPress.Post

let url_re = Pcre.regexp "^http://(www.)?saasta.fi/(.*)p=([0-9]+)$"

(* Query post title & poster via xmlrpc.php directly from the site *)
let find_post_author post =
  let m = (Pcre.extract ~rex:url_re post) in
  let post_id = int_of_string m.(3) in
  P.printf "query post #%5i" post_id;
  flush_all ();
  let p = wp#get_post post_id in
  P.printf " author: %-10s post title: '%s'\n" 
    p.WPPost.wp_author_display_name p.WPPost.title;
  p.WPPost.wp_author_display_name

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
              let poster = find_post_author post in
              let old_poster_score = 
                default_find SMap.find poster poster_score 0 in
              let score = rank_score ndx in
              let post_histo' = SMap.add post (hist+1) post_histo in
              let post_score' = SMap.add post (old_score+score) post_score in
              let poster_score' = 
                SMap.add poster (old_poster_score+score) poster_score in
              (post_histo', post_score', poster_score')) 
           (post_histo, post_score, poster_score) vote)
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
