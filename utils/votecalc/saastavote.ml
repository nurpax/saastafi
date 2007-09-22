
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

  let dir = Unix.opendir "votes" in
  let rec loop acc =
    try 
      let fname = Unix.readdir dir in
      if fname <> "." && fname <> ".." then
        loop (read_votes ("votes/"^fname)::acc)
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

let post_author = 
  List.fold_left (fun acc (post,auth) -> SMap.add post auth acc)
    SMap.empty
    [("http://www.saasta.fi/saasta/?p=467", "htimo");
     ("http://www.saasta.fi/saasta/?p=459", "sampo");
     ("http://www.saasta.fi/saasta/?p=858", "jasin");
     ("http://www.saasta.fi/saasta/?p=781", "jasin");
     ("http://www.saasta.fi/saasta/?p=733", "htimo");
     ("http://www.saasta.fi/saasta/?p=670", "sampo");
     ("http://www.saasta.fi/saasta/?p=664", "yaro");

     ("http://www.saasta.fi/saasta/?p=691", "muumi");
     ("http://www.saasta.fi/saasta/?p=860", "jasin");
     ("http://www.saasta.fi/saasta/?p=770", "muumi");
     ("http://www.saasta.fi/saasta/?p=308", "sampo");
     ("http://www.saasta.fi/saasta/?p=346", "niko");
     ("http://www.saasta.fi/saasta/?p=473", "niko");
     ("http://www.saasta.fi/saasta/?p=418", "niko");
     ("http://www.saasta.fi/saasta/?p=866", "jasin");
     ("http://www.saasta.fi/saasta/?p=235", "jasin");

     ("http://www.saasta.fi/saasta/?p=432", "iiro");
     ("http://www.saasta.fi/saasta/?p=537", "jasin");
     ("http://www.saasta.fi/saasta/?p=256", "iiro");
     ("http://www.saasta.fi/saasta/?p=790", "jugi");
     ("http://www.saasta.fi/saasta/?p=462", "niko");
     ("http://www.saasta.fi/saasta/?p=306", "teemu");
     ("http://www.saasta.fi/saasta/?p=233", "jasin");
     ("http://www.saasta.fi/saasta/?p=783", "sampo");
     ("http://www.saasta.fi/saasta/?p=716", "niko");
     ("http://www.saasta.fi/saasta/?p=541", "htimo");
     ("http://www.saasta.fi/saasta/?p=713", "wili");
     ("http://www.saasta.fi/saasta/?p=489", "jasin");
     ("http://www.saasta.fi/saasta/?p=162", "muumi");
     ("http://www.saasta.fi/saasta/?p=223", "muumi");
     ("http://www.saasta.fi/saasta/?p=764", "muumi");
     ("http://www.saasta.fi/saasta/?p=570", "htimo");
     ("http://www.saasta.fi/saasta/?p=191", "andrew");
     ("http://www.saasta.fi/saasta/?p=661", "jasin");
     ("http://www.saasta.fi/saasta/?p=196", "sampo");
     ("http://www.saasta.fi/saasta/?p=186", "muumi");
     ("http://www.saasta.fi/saasta/?p=191", "andrew");

     ("http://www.saasta.fi/saasta/?p=201", "sampo");
     ("http://www.saasta.fi/saasta/?p=204", "sampo");
     ("http://www.saasta.fi/saasta/?p=544", "jasin");
     ("http://www.saasta.fi/saasta/?p=492", "meri");
     ("http://www.saasta.fi/saasta/?p=244", "sampo");
     ("http://www.saasta.fi/saasta/?p=814", "yaro");
     ("http://www.saasta.fi/saasta/?p=816", "htimo");
     ("http://www.saasta.fi/saasta/?p=521", "sampo");
     ("http://www.saasta.fi/saasta/?p=199", "sampo");
     ("http://www.saasta.fi/saasta/?p=279", "muumi");
     ("http://www.saasta.fi/saasta/?p=785", "jasin");
     ("http://www.saasta.fi/saasta/?p=496", "iiro");
     ("http://www.saasta.fi/saasta/?p=461", "niko");
     ("http://www.saasta.fi/saasta/?p=126", "janne");
     ("http://www.saasta.fi/saasta/?p=188", "muumi");
     ("http://www.saasta.fi/saasta/?p=175", "janne");
     ("http://www.saasta.fi/saasta/?p=635", "jasin");
     ("http://www.saasta.fi/saasta/?p=194", "muumi");

     ("http://www.saasta.fi/saasta/?p=673", "muumi");
     ("http://www.saasta.fi/saasta/?p=474", "janne");
     ("http://www.saasta.fi/saasta/?p=367", "jussi");
     ("http://www.saasta.fi/saasta/?p=750", "jasin");

     ("http://www.saasta.fi/saasta/?p=731", "jasin");
     ("http://www.saasta.fi/saasta/?p=579", "jasin");
     ("http://www.saasta.fi/saasta/?p=863", "muumi");

     ("http://www.saasta.fi/saasta/?p=283", "wili");
     ("http://www.saasta.fi/saasta/?p=393", "wili");
     ("http://www.saasta.fi/saasta/?p=556", "jasin");
     ("http://www.saasta.fi/saasta/?p=693", "muumi");

     ("http://www.saasta.fi/saasta/?p=831", "muumi");
     ("http://www.saasta.fi/saasta/?p=718", "jasin");
     ("http://www.saasta.fi/saasta/?p=830", "muumi");
     ("http://www.saasta.fi/saasta/?p=787", "muumi");

     ("http://www.saasta.fi/saasta/?p=292", "sampo");
     ("http://www.saasta.fi/saasta/?p=705", "muumi");
    ]

let find_post_author post = 
  try 
    SMap.find post post_author 
  with 
    Not_found ->
      P.printf "** couldn't find author for %s\n" post;
      "unknown"

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
