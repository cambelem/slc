-- Change tables to use InnoDB engine
ALTER TABLE slc_visit ENGINE=InnoDB;
ALTER TABLE slc_issue ENGINE=InnoDB;
ALTER TABLE slc_visit_issue_index ENGINE=InnoDB;

-- Add more referrals to the list
UPDATE slc_referral_type SET name="Internet" where id=16;
UPDATE slc_referral_type SET name="Off-campus Presentation" where id=17;
UPDATE slc_referral_type SET name="Orientation" where id=18;

-- Add a new landlord
INSERT INTO slc_landlord (id, name) VALUES (999, 'Other/Unspecified');

-- Changed column heading
ALTER TABLE slc_visit change c_id client_id varchar(85) not null;
