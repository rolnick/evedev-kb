<?php
if (isset($_SESSION['sso_kill_id']) && isset($_SESSION['sso_char']))
{
  $returnUrl = edkURI::page("kill_detail", $_SESSION['sso_kill_id'], "kll_id");
  $comments = new Comments($_SESSION['sso_kill_id']);
  $comments->addComment($_SESSION['sso_char']->getName(), $_POST['sso_comment']);
  header('Location: '.htmlspecialchars_decode($returnUrl));
  die();
}
?>
