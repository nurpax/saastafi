# Introduction #

Below is a bunch of more or less useful SQL queries we can use for gathering stats and whatnot.

## Top X of faved posts ##
```
select DISTINCT p.post_title as title,COUNT(p.post_title) as fave_count,f.post_id as post_id,dATE_FORMAT(p.post_date, '%b %d, %Y') as date_posted from saasta_posts p,saasta_faves f where f.post_id=p.ID AND DATE(p.post_date) >= '2007-10-01' AND DATE(p.post_date) < '2008-01-01' GROUP BY p.post_title ORDER BY fave_count DESC;
```

## Top X faved posts part deux ##
The same but a bit more advanced - uses QUARTER() to determine range of posts and also outputs post author and direct (although hardcoded) link to the post. Replace QUARTER(NOW()) and YEAR(NOW()) with fixed values (e.g. 4 and 2007, respectively) to come up with stats for any given quarter.

Update 2008-02-03: Now also ignores faves to own posts (p.post\_author <> f.user\_id)

```
SELECT DISTINCT 
       p.post_title AS title,	
       u.display_name AS author,
       COUNT(p.post_title) AS fave_count,
       f.post_id AS post_id,
       CONCAT('http://saasta.fi/saasta/?p=',f.post_id) AS url,
       DATE_FORMAT(p.post_date, '%b %d, %Y') AS date_posted
FROM 
     saasta_posts p,
     saasta_faves f,
     saasta_users u
WHERE 
      f.post_id=p.ID AND 
      p.post_author <> f.user_id AND
      p.post_author=u.ID AND      
      QUARTER(p.post_date)=QUARTER(NOW()) AND
      YEAR(p.post_date)=YEAR(NOW())
GROUP BY 
      p.post_title 
ORDER BY 
      fave_count DESC;
```

## Posts per user in quarter ##

Select _current_ (depends on the date!) quarter number of posts per user. Replace QUARTER(NOW()) and YEAR(NOW()) with fixed values (e.g. 4 and 2007, respectively) to come up with stats for any given quarter.

```
SELECT su.display_name as name, 
       count(sp.ID) as num_posts
FROM saasta_posts sp, 
     saasta_users su 
WHERE sp.post_status='publish' AND 
      su.ID=sp.post_author AND
      QUARTER(sp.post_date)=QUARTER(NOW()) AND
      YEAR(sp.post_date)=YEAR(NOW())
GROUP BY 
      su.ID 
ORDER BY 
      num_posts desc, 
      name;
```

## Top 5 tags ##

```
SELECT
	term.name as tag,
	tax.count as num_posts
FROM 
     saasta_terms term,
     saasta_term_taxonomy tax
WHERE
	tax.term_id = term.term_id AND
	tax.taxonomy = 'post_tag' AND
	tax.count > 0
ORDER BY
      num_posts DESC,
      tag
LIMIT 1,5
```