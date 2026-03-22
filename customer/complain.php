<?php
include('../connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$type = mysqli_real_escape_string($connect, $_POST['type']);
$text = mysqli_real_escape_string($connect, $_POST['text']);

$sql = "INSERT INTO complaint (type, text)
VALUES ('$type', '$text')";

if (mysqli_query($connect, $sql)) {
echo "✔ Submitted successfully!";
} else {
echo "✖ Error submitting!";
}
}
?>
<!-- Complaint Modal -->
<div id="complaintModal" class="modal">
<div class="modal-content">
<span class="close">&times;</span>
<h2><i class="fas fa-comment-dots"></i> Complaint / Feedback</h2>
<link rel="stylesheet" href="css/complain.css">
<form id="complaintForm">
<select name="type" required>
    <option value="">Select Type</option>
    <option value="Complaint">Complaint</option>
    <option value="Feedback">Feedback</option>
    <option value="Suggestion">Suggestion</option>
</select>

<textarea name="text" rows="4" placeholder="Write your message..." required></textarea>

<button type="submit"><i class="fas fa-paper-plane"></i> Submit</button>
<div id="messageBox"></div>
</form>
</div>
</div>


