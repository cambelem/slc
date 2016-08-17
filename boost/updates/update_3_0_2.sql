INSERT INTO slc_problem VALUES("Problem", 48, "Immigration", "Other -> ");

UPDATE slc_problem SET description = "Family / Relationship" WHERE id = 36;

INSERT INTO slc_problem VALUES("Problem", 48, "Student Organization", "Other -> ");
DELETE FROM slc_problem WHERE id = 999 and description = "Other";

ALTER TABLE slc_landlord modify column id int(11) auto_increment;
ALTER TABLE slc_landlord ADD UNIQUE (name);
