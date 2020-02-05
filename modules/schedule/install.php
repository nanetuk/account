<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'schedule')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedule` (
  `id` int(11) NOT NULL,
  `summary` varchar(400) NOT NULL,
  `description` text NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` int(11) NOT NULL,
  `notified` int(11) NOT NULL DEFAULT '0',
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
