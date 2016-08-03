ALTER TABLE slc_landlord modify column id int(11) auto_increment;
ALTER TABLE slc_landlord ADD UNIQUE (name);