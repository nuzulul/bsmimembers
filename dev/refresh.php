<?php
echo "update repo ... </br></br>";

echo shell_exec("git fetch --all");
echo shell_exec("git reset --hard origin/main");

file_put_contents("php://stdout", "\nupdate repo ...\n");

?>