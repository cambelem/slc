-- slc - phpwebsite module
-- @version $Id: $
-- @author Adam Dixon & Eric Cambel
BEGIN;
CREATE TABLE IF NOT EXISTS slc_visit (
    id              int(8) not null auto_increment,
    initial_date    int not null,
    client_id       varchar(85) not null,
    PRIMARY KEY (id)
)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS slc_issue (
    id              int not null,
    problem_id      int not null,
    landlord_id     int,
    PRIMARY KEY   (id)
)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS slc_visit_issue_index (
    id              int not null,
    v_id            int not null,
    i_id            int not null,
    counter         int default 1,
    resolve_date    int,
    last_access	    int,
    PRIMARY KEY   (id)
)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS slc_client (
    id              varchar(85) not null,
    first_visit     int not null,
    classification  varchar(50),
    living_location varchar(50),
    major	    varchar(50),
    referral	    int,
    PRIMARY KEY   (id)
);

CREATE TABLE IF NOT EXISTS slc_landlord (
    id		    int not null,
    name	    varchar(100) not null,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS slc_problem (
     type            varchar(50),
     id		     int not null,
     description     varchar(100) not null,
     tree            varchar(100),
     PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS slc_referral_type (
     id 	int not null,
     name	varchar(100) not null,
     PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS slc_student_data (
	id 	 int not null,
	fname	 varchar(50),
	lname	 varchar(50),
	username varchar(50),
	classification	 varchar(50),
	major	 varchar(50),
	living_location	varchar(50),
	PRIMARY KEY (id)
);

-- Referral Types
INSERT INTO slc_referral_type VALUES(1, "Friend/Word of Mouth");
INSERT INTO slc_referral_type VALUES(2, "Former Client");
INSERT INTO slc_referral_type VALUES(3, "Parents");
INSERT INTO slc_referral_type VALUES(4, "Off-Campus Community Relations Office");
INSERT INTO slc_referral_type VALUES(5, "Student Conduct");
INSERT INTO slc_referral_type VALUES(6, "Housing, Res. Life");
INSERT INTO slc_referral_type VALUES(7, "Counseling Center");
INSERT INTO slc_referral_type VALUES(8, "Academic Advisor");
INSERT INTO slc_referral_type VALUES(9, "Professor");
INSERT INTO slc_referral_type VALUES(10, "ASU PD");
INSERT INTO slc_referral_type VALUES(11, "Community Source");
INSERT INTO slc_referral_type VALUES(12, "Sign on Door");
INSERT INTO slc_referral_type VALUES(13, "Flyer in Residence Hall");
INSERT INTO slc_referral_type VALUES(14, "Other Advertising");
INSERT INTO slc_referral_type VALUES(15, "Other Referral");
INSERT INTO slc_referral_type VALUES(16, "Internet");
INSERT INTO slc_referral_type VALUES(17, "Off-campus presentation");
INSERT INTO slc_referral_type VALUES(18, "Orientation");
INSERT INTO slc_referral_type VALUES(19, "Meet and Greet packet");


-- Landlords
INSERT INTO slc_landlord VALUES (1, "Acme Realty Co");
INSERT INTO slc_landlord VALUES (2, "Alpha Realty Mgmt");
INSERT INTO slc_landlord (id, name) VALUES (999, 'Other / Unspecified');

-- Problems


INSERT INTO slc_problem VALUES("Conditions", 1, "Mold", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 2, "Plumbing / Water Supply", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 3, "Infestations", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 4, "Run-down", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 5, "Flooding / Roof Leaks", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 6, "Security", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 7, "Exterior", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 8, "Construction", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 9, "Heating System", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 10, "Contamination", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 11, "Smoke Detector", "Landlord-Tenant -> Condition -> ");
INSERT INTO slc_problem VALUES("Conditions", 12, "Other Condition", "Landlord-Tenant -> Condition -> ");
		
INSERT INTO slc_problem VALUES("Landlord-Tenant", 13, "Fees", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 14, "Rent Dispute", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 15, "Security Deposit", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 16, "Tenancy / Eviction", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 17, "Subletting", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 18, "Lease Questions", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 19, "Roommate", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 20, "Amenities", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 21, "Lease Dispute", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 22, "Rules / Regulations", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 23, "Appliances", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 47, "Zoning", "Landlord-Tenant -> ");
INSERT INTO slc_problem VALUES("Landlord-Tenant", 24, "Other Landlord", "Landlord-Tenant -> ");
 
INSERT INTO slc_problem VALUES("Law Enforcement Agency", 25, "ASU", "Criminal -> Agency -> ");
INSERT INTO slc_problem VALUES("Law Enforcement Agency", 26, "Boone Police Department", "Criminal -> Agency -> ");
INSERT INTO slc_problem VALUES("Law Enforcement Agency", 27, "Sheriff", "Criminal -> Agency -> ");
INSERT INTO slc_problem VALUES("Law Enforcement Agency", 28, "State Trooper", "Criminal -> Agency -> ");
INSERT INTO slc_problem VALUES("Law Enforcement Agency", 29, "ALE", "Criminal -> Agency -> ");

INSERT INTO slc_problem VALUES("Type of Criminal Problem", 30, "Aggressive", "Criminal -> Type -> ");
INSERT INTO slc_problem VALUES("Type of Criminal Problem", 31, "Search / Seizure", "Criminal -> Type -> ");
INSERT INTO slc_problem VALUES("Type of Criminal Problem", 32, "Misleading", "Criminal -> Type -> ");
INSERT INTO slc_problem VALUES("Type of Criminal Problem", 33, "Private Contractor", "Criminal -> Type -> ");
INSERT INTO slc_problem VALUES("Type of Criminal Problem", 34, "Incorrect Action / No Basis", "Criminal -> Type -> ");
		
INSERT INTO slc_problem VALUES("Problem", 35, "Traffic", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 36, "Family", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 37, "Civil Suits for Money", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 38, "Employment", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 39, "Consumer", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 40, "Contract", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 41, "Civil Rights / Discrimination", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 42, "Victim of Crime", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 43, "Corporation", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 44, "Name Change", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 45, "Copyright / Invention", "Other -> ");
INSERT INTO slc_problem VALUES("Problem", 46, "Other Problem", "Other -> ");

INSERT INTO slc_problem VALUES("Generic", 997, "Landlord-Tenant", "");
INSERT INTO slc_problem VALUES("Generic", 996, "Conditions", "");
INSERT INTO slc_problem VALUES("Generic", 995, "Law Enforcement Agency", "");
INSERT INTO slc_problem VALUES("Generic", 998, "Criminal", "");
INSERT INTO slc_problem VALUES("Generic", 999, "Other", "");

COMMIT;
