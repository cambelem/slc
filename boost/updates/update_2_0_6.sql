-- Drop empty/unused/never-referenced tables
DROP TABLE  slc_client_detail_index,
            slc_detail,
            slc_issue_detail_index,
            slc_visit_detail_index,
            slc_visit_issue_date_index;

-- Delete issue records with problem IDs that don't exist.
DELETE slc_issue.*
FROM slc_issue
WHERE slc_issue.problem_id NOT IN (
    SELECT slc_problem.id FROM slc_problem
);

-- Delete visit records with client IDs that don't exist.
DELETE slc_visit.*
FROM slc_visit
WHERE slc_visit.c_id NOT IN (
    SELECT id FROM slc_client
);

-- Delete slc_visit_issue_index records with issue IDs that don't exist.
DELETE slc_visit_issue_index.*
FROM slc_visit_issue_index
WHERE slc_visit_issue_index.i_id NOT IN (
    SELECT slc_issue.id FROM slc_issue
);

-- Delete slc_visit_issue_index records with visit IDs that don't exist.
DELETE slc_visit_issue_index.*
FROM slc_visit_issue_index
WHERE slc_visit_issue_index.v_id NOT IN (
    SELECT slc_visit.id FROM slc_visit
);

-- Delete visit records without an associated slc_visit_issue_index record.
DELETE slc_visit.* 
FROM slc_visit 
WHERE slc_visit.id NOT IN (
    SELECT v_id FROM slc_visit_issue_index
);

-- Delete issue records without an associated slc_visit_issue_index record.
DELETE slc_issue.* 
FROM slc_issue 
WHERE slc_issue.id NOT IN (
    SELECT i_id FROM slc_visit_issue_index
);

-- Then delete client records without associated visits.
DELETE slc_client.*
FROM slc_client
WHERE slc_client.id NOT IN (
    SELECT c_id FROM slc_visit
);

-- Merge all 'Criminal' sub-types into the main 'Criminal' type
UPDATE slc_issue
SET problem_id=998
WHERE problem_id IN (
    25,26,27,28,29,30,31,32,33,34,995
);
