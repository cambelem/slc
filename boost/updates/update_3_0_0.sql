-- Change tables to use InnoDB engine
ALTER TABLE slc_visit ENGINE=InnoDB;
ALTER TABLE slc_issue ENGINE=InnoDB;
ALTER TABLE slc_visit_issue_index ENGINE=InnoDB;

-- Add more referrals to the list
UPDATE slc_referral_type SET name="Internet" where id=16;
UPDATE slc_referral_type SET name="Off-campus Presentation" where id=17;
UPDATE slc_referral_type SET name="Orientation" where id=18;

-- Add a new landlord
INSERT INTO slc_landlord (id, name) VALUES (999, 'Other / Unspecified');

-- Changed column heading
ALTER TABLE slc_visit change c_id client_id varchar(85) not null;

-- Changed the slc_visit table to auto_increment
ALTER TABLE slc_visit MODIFY COLUMN id INT(11) auto_increment;

-- Specified the counter column to start at 0
ALTER TABLE slc_visit_issue_index MODIFY COLUMN counter int(11) default 0;

-- Needed to decrement the counter column by 1 to start the followups at 0
UPDATE slc_visit_issue_index SET counter = counter - 1;