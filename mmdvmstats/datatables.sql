CREATE TABLE files(id int primary key, name varchar(100));
CREATE TABLE log(id int primary key, ts datetime, call varchar(10), duration decimal(5,2));

