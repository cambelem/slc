update slc_issue set landlord_id = NULL where problem_id in (select id from slc_problem where tree="Other ->" or description="criminal");

insert into slc_referral_type values(20,"Electronic Signboard");
insert into slc_referral_type values(21,"Other Student Development Office");

delete from slc_referral_type where name = "Off-Campus Community Relations Office";
delete from slc_referral_type where name = "Meet and Greet packet";

update slc_referral_type set name = "Presentation" where name = "Off-campus presentation";