CREATE TABLE %dbprefix%bill_payment_r (bill_payment_id INT(11) NOT NULL AUTO_INCREMENT , bill_id INT(11) NOT NULL , payment_id INT(11) NOT NULL , PRIMARY KEY (bill_payment_id));
ALTER TABLE %dbprefix%bill_payment_r ADD adjust_amount DECIMAL(11) NULL AFTER payment_id;
INSERT INTO %dbprefix%bill_payment_r (bill_id,payment_id,adjust_amount) SELECT bill_id,payment_id,pay_amount FROM %dbprefix%payment;
ALTER TABLE %dbprefix%payment CHANGE bill_id patient_id INT(11) NOT NULL;
UPDATE %dbprefix%payment AS payment SET patient_id = (SELECT bill.patient_id FROM %dbprefix%bill AS bill INNER JOIN %dbprefix%bill_payment_r AS bill_payment_r ON bill_payment_r.bill_id = bill.bill_id WHERE bill_payment_r.payment_id = payment.payment_id );
CREATE OR REPLACE VIEW %dbprefix%view_payment AS SELECT DISTINCT payment.payment_id,payment.pay_date,payment.pay_mode,payment.cheque_no,payment.pay_amount,patient.patient_id,patient.display_id,contacts.first_name,contacts.middle_name,contacts.last_name   FROM %dbprefix%payment AS payment	       INNER JOIN %dbprefix%patient as patient ON patient.patient_id = payment.patient_id 	   INNER JOIN %dbprefix%contacts as contacts ON contacts.contact_id = patient.contact_id;
CREATE OR REPLACE VIEW %dbprefix%view_bill AS SELECT bill.bill_id AS bill_id,        bill.bill_date AS bill_date,	   bill.visit_id AS visit_id,	   users.name AS doctor_name,	   visit.userid AS userid,	   patient.patient_id AS patient_id,	   patient.display_id AS display_id,	   contacts.first_name AS first_name,	   contacts.middle_name AS middle_name,	   contacts.last_name AS last_name,	   bill.total_amount AS total_amount,	   bill.due_amount AS due_amount,	   SUM(payment.pay_amount) AS pay_amount   FROM %dbprefix%bill AS bill        join %dbprefix%visit AS visit ON bill.visit_id = visit.visit_id  	   join %dbprefix%users As users on visit.userid = users.userid 	   join %dbprefix%patient AS patient on bill.patient_id = patient.patient_id 	   INNER JOIN %dbprefix%bill_payment_r AS bill_payment_r ON bill_payment_r.bill_id = bill.bill_id	   join %dbprefix%payment AS payment on payment.payment_id = bill_payment_r.payment_id 	   join %dbprefix%contacts AS contacts on contacts.contact_id = patient.contact_id GROUP BY bill.bill_id,users.name,visit.userid, patient.patient_id;
UPDATE %dbprefix%version SET current_version='0.2.8';