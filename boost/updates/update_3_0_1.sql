-- Moved relevant columns from slc_visit_issue_index to slc_issue
ALTER TABLE slc_issue ADD COLUMN v_id int NOT NULL DEFAULT 0;
ALTER TABLE slc_issue ADD COLUMN counter int DEFAULT 0;

DROP TABLE slc_visit_issue_index;

DELETE FROM slc_problem WHERE id = 996;
DELETE FROM slc_problem WHERE id = 997;
