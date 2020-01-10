<?php declare(strict_types=1);

$touchedFiles = [];
$exitCode = 1;

$gitGetChanges = 'git diff --cached --name-only --diff-filter=ACMR master';
exec($gitGetChanges, $touchedFiles, $exitCode);

if ($exitCode !== 0) {
    echo "Command terminated with status: \"$exitCode\" \n";
    exit($exitCode);
}

$phpFiles = array_filter($touchedFiles, function ($f) {
    return preg_match('/.*\.php$/', $f);
});

$csFixerParam = implode(' ', $phpFiles);

echo shell_exec("php-cs-fixer fix -vv $csFixerParam");
