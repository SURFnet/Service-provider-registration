INSERT INTO janus.`janus__user` (`userid`, `type`, `email`, `active`, `update`, `created`, `ip`, `data`, `secret`)
VALUES ('admin','a:2:{i:0;s:9:\"technical\";i:1;s:5:\"admin\";}',NULL,'yes',NULL,NOW(),'127.0.0.1',NULL,NULL);
INSERT INTO janus.`janus__user` (`userid`, `type`, `email`, `active`, `update`, `created`, `ip`, `data`, `secret`)
VALUES ('spform','a:2:{i:0;s:9:\"technical\";i:1;s:5:\"admin\";}',NULL,'yes',NULL,NOW(),'127.0.0.1',NULL,'spform');

DROP DATABASE IF EXISTS surf;
CREATE DATABASE surf;
