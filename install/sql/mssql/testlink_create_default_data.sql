--  -----------------------------------------------------------------------------------
--  TestLink Open Source Project - http://testlink.sourceforge.net/
--  $Id: testlink_create_default_data.sql,v 1.8 2007/05/05 18:53:04 schlundus Exp $
--  SQL script - create default data (rights & admin account)
--  
--  Database Type: Microsoft SQL Server
--  20070126 - franciscom - add new rights to admin role
--
--  -----------------------------------------------------------------------------------

--  admin account 
--  SECURITY: change password after first login
USE [testlink]

INSERT INTO users (login,password,role_id,email,first,last,locale,active)
             VALUES ('admin','21232f297a57a5a743894a0e4a801fc3', 8,'', 'Testlink', 'Administrator', 'en_GB',1);

SET IDENTITY_INSERT node_types ON

--  Node types -
INSERT INTO node_types (id,description) VALUES (1, 'testproject');
INSERT INTO node_types (id,description) VALUES (2, 'testsuite');
INSERT INTO node_types (id,description) VALUES (3, 'testcase');
INSERT INTO node_types (id,description) VALUES (4, 'testcase_version');
INSERT INTO node_types (id,description) VALUES (5, 'testplan');

-- 20070113 - franciscom - new node_types
INSERT INTO node_types (id,description) VALUES (6, 'requirement_spec');
INSERT INTO node_types (id,description) VALUES (7, 'requirement');


SET IDENTITY_INSERT node_types OFF

--  Roles -
SET IDENTITY_INSERT roles ON
INSERT INTO roles (id,description) VALUES (8, 'admin');
INSERT INTO roles (id,description) VALUES (9, 'leader');
INSERT INTO roles (id,description) VALUES (6, 'senior tester');
INSERT INTO roles (id,description) VALUES (7, 'tester');
INSERT INTO roles (id,description) VALUES (5, 'guest');
INSERT INTO roles (id,description) VALUES (4, 'test designer');
INSERT INTO roles (id,description) VALUES (3, '<no rights>');
SET IDENTITY_INSERT roles OFF

--  Rights - 
SET IDENTITY_INSERT rights ON
INSERT INTO rights (id,description) VALUES (1 ,'testplan_execute'      );
INSERT INTO rights (id,description) VALUES (2 ,'testplan_create_build' );
INSERT INTO rights (id,description) VALUES (3 ,'testplan_metrics'      );
INSERT INTO rights (id,description) VALUES (4 ,'testplan_planning'     );
INSERT INTO rights (id,description) VALUES (5 ,'testplan_assign_rights');
INSERT INTO rights (id,description) VALUES (6 ,'mgt_view_tc'           );
INSERT INTO rights (id,description) VALUES (7 ,'mgt_modify_tc'         );
INSERT INTO rights (id,description) VALUES (8 ,'mgt_view_key'          );
INSERT INTO rights (id,description) VALUES (9 ,'mgt_modify_key'        );
INSERT INTO rights (id,description) VALUES (10,'mgt_view_req'          );
INSERT INTO rights (id,description) VALUES (11,'mgt_modify_req'        );
INSERT INTO rights (id,description) VALUES (12,'mgt_modify_product'    );
INSERT INTO rights (id,description) VALUES (13,'mgt_users'             );
INSERT INTO rights (id,description) VALUES (14,'role_management'       );
INSERT INTO rights (id,description) VALUES (15,'user_role_assignment'  );
INSERT INTO rights (id,description) VALUES (16,'mgt_testplan_create'	);
INSERT INTO rights (id,description) VALUES (17,'cfield_view');
INSERT INTO rights (id,description) VALUES (18,'cfield_management');
INSERT INTO rights (id,description) VALUES (19,'testplan_user_role_assignment'  );
SET IDENTITY_INSERT rights OFF


--  Rights for Administrator (admin role)
INSERT INTO role_rights (role_id,right_id) VALUES (8,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,4 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,5 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,9 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,10);
INSERT INTO role_rights (role_id,right_id) VALUES (8,11);
INSERT INTO role_rights (role_id,right_id) VALUES (8,12);
INSERT INTO role_rights (role_id,right_id) VALUES (8,13);
INSERT INTO role_rights (role_id,right_id) VALUES (8,14);
INSERT INTO role_rights (role_id,right_id) VALUES (8,15);
INSERT INTO role_rights (role_id,right_id) VALUES (8,16);
-- 20070126 - franciscom
INSERT INTO role_rights (role_id,right_id) VALUES (8,17);
INSERT INTO role_rights (role_id,right_id) VALUES (8,18);


--  Rights for guest (guest role)
INSERT INTO role_rights (role_id,right_id) VALUES (5,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (5,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (5,8 );

--  Rights for test dsigner (test designer role)
INSERT INTO role_rights (role_id,right_id) VALUES (4,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,11);

--  Rights for tester (tester role)
INSERT INTO role_rights (role_id,right_id) VALUES (7,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (7,3 );

--  Rights for senior tester (senior tester role)
INSERT INTO role_rights (role_id,right_id) VALUES (6,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,11);

--  Rights for leader (leader role)
INSERT INTO role_rights (role_id,right_id) VALUES (9,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,4 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,5 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,9 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,11);
INSERT INTO role_rights (role_id,right_id) VALUES (9,15);
INSERT INTO role_rights (role_id,right_id) VALUES (9,16);

-- Assignment types
SET IDENTITY_INSERT assignment_types ON
INSERT INTO assignment_types (id,fk_table,description) VALUES(1,'testplan_tcversions','testcase_execution');
INSERT INTO assignment_types (id,fk_table,description) VALUES(2,'tcversions','testcase_review');
SET IDENTITY_INSERT assignment_types OFF

-- Assignment status
SET IDENTITY_INSERT assignment_status ON
INSERT INTO assignment_status (id,description) VALUES(1,'open');
INSERT INTO assignment_status (id,description) VALUES(2,'closed');
INSERT INTO assignment_status (id,description) VALUES(3,'completed');
INSERT INTO assignment_status (id,description) VALUES(4,'todo_urgent');
INSERT INTO assignment_status (id,description) VALUES(5,'todo');
SET IDENTITY_INSERT assignment_types OFF


--  Database version
--
-- Dumping data for table db_version
--
INSERT INTO "db_version" ("version","upgrade_ts") VALUES ('1.7.0 Beta 3',GETDATE());
