ALTER TABLE `patients`
ADD COLUMN `status` ENUM('Active', 'Discharged', 'Deceased') NOT NULL DEFAULT 'Active' AFTER `blood_type`;
