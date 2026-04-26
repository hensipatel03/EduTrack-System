<?php
// admin/reports/usage_report.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$counts = [];
$counts['total_users']    = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$counts['admins']         = (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'admin'")->fetchColumn();
$counts['faculty']        = (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'faculty'")->fetchColumn();
$counts['students']       = (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'student'")->fetchColumn();
$counts['courses']        = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$counts['branches']       = (int)$pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn();
$counts['semesters']      = (int)$pdo->query("SELECT COUNT(*) FROM semesters")->fetchColumn();
$counts['subjects']       = (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$counts['materials']      = (int)$pdo->query("SELECT COUNT(*) FROM study_materials")->fetchColumn();
$counts['assignments']    = (int)$pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
$counts['submissions']    = (int)$pdo->query("SELECT COUNT(*) FROM assignment_submissions")->fetchColumn();
$counts['doubts']         = (int)$pdo->query("SELECT COUNT(*) FROM doubts")->fetchColumn();
$counts['doubts_open']    = (int)$pdo->query("SELECT COUNT(*) FROM doubts WHERE status = 'open'")->fetchColumn();
$counts['doubts_resolved']= (int)$pdo->query("SELECT COUNT(*) FROM doubts WHERE status = 'resolved'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Usage Report</h3>

<table class="table table-bordered table-sm w-auto">
    <tbody>
    <tr><th>Total Users</th><td><?php echo $counts['total_users']; ?></td></tr>
    <tr><th>Admins</th><td><?php echo $counts['admins']; ?></td></tr>
    <tr><th>Faculty</th><td><?php echo $counts['faculty']; ?></td></tr>
    <tr><th>Students</th><td><?php echo $counts['students']; ?></td></tr>
    <tr><th>Courses</th><td><?php echo $counts['courses']; ?></td></tr>
    <tr><th>Branches</th><td><?php echo $counts['branches']; ?></td></tr>
    <tr><th>Semesters</th><td><?php echo $counts['semesters']; ?></td></tr>
    <tr><th>Subjects</th><td><?php echo $counts['subjects']; ?></td></tr>
    <tr><th>Study Materials</th><td><?php echo $counts['materials']; ?></td></tr>
    <tr><th>Assignments</th><td><?php echo $counts['assignments']; ?></td></tr>
    <tr><th>Submissions</th><td><?php echo $counts['submissions']; ?></td></tr>
    <tr><th>Doubts (Total)</th><td><?php echo $counts['doubts']; ?></td></tr>
    <tr><th>Doubts Open</th><td><?php echo $counts['doubts_open']; ?></td></tr>
    <tr><th>Doubts Resolved</th><td><?php echo $counts['doubts_resolved']; ?></td></tr>
    </tbody>
</table>

