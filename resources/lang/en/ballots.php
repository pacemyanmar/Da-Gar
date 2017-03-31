<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the paginator library to build
    | the simple pagination links. You are free to change them to anything
    | you want to customize your views to better match your application.
    |
     */

    'serial' => 'Serial',
    'candidate' => 'Candidate',
    'party' => 'Party',
    'votes_cast' => 'Votes Cast',
    'polling_station' => 'Polling Station',
    'advanced_voting' => 'Advanced Voting',
    'total_cast' => 'Total Cast',
    'in_numbers' => 'In numbers',
    'in_words' => 'In words',
    'remarks' => 'Remarks',
    'ballots_issued_on_e_day' => '1.Ballots issued on e-day',
    'ballots_issued' => '1.Ballots issued',
    'ballots_received' => '2.Ballots received',
    'ballots_received_for_advanced_voting' => '2.Ballots received for advanced voting',
    'valid_advanced' => '3 - Valid ballots',
    'invalid_advanced' => '4 - Invalid ballots',
    'missing_advanced' => '5 - Missing ballots',
    'valid' => '3.Valid',
    'invalid' => '4.Invalid',
    'missing' => '5.Missing',
    'witnesses' => 'Witnesses',
    'log1' => 'Advanced votes for NDL and USDP should be less than overall # of advanced votes. Check Parties advanced votes and Remarks 2. { Adv(USDP) + Adv(NLD) > Rem(2) }',
    'log2' => 'Total ballots issued/cast <> total ballots counted. { Rem(1) + Rem(2) != Rem(3) + Rem(4) + Rem(5) } Check remarks 1 to 5.',
    'log3' => 'High proportion of invalid votes compared to all cast votes. { Rem(4) / (Rem(1) + Rem(2)) > 0.15 } Check remarks 1,2 and 4. Ratio is',
    'log4' => 'High proportion of missing votes compared to all cast votes. { Rem(5) / (Rem(1) + Rem(2)) > 0.15 } Check remarks 1,2 and 5. Ratio is',
    'log5' => 'High proportion of advanced votes compared to all cast votes. { Rem(2) / (Rem(1) + Rem(2)) > 0.1 } Check remarks 1 and 2. Ratio is',
    'log6' => 'More votes than registered voters. { EA < (Rem(1) + Rem(2)) } Check remarks 1,2 and EA.',
    'log7' => 'Different number in Form 13 and Form 16. { EB != Rem(2) } Check remarks 2 and EB.',
    'log8' => 'High proportion of advanced votes compared to registered voters. { EB / (EA + EB) > 0.1 } Check EA and EB values. Ratio is',
    'log9' => 'Check remarks 2, 3 and 4.',
    'log10' => 'Check USDP and NLD advanced votes and Remarks 3.',
    'log11' => 'Check remarks 1, 2 and 5.',
    'log12' => 'Check remarks 2, 3 and 4.',

];
