<?php
session_start();

// Delete the session variable, effectively logging out the user
unset($_SESSION["currentUserEmail"]);
