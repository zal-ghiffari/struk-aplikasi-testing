<?php

namespace App;

class EntryPrinter
{
    function print(array $entries) {
        $mask = "|%-10s |%10s" . PHP_EOL;
        printf("\n");
        foreach ($entries as $entry) {
            printf($mask, $entry['account'], $entry['amount']);
        }
    }
}
