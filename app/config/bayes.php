<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Bayes Settings
    |--------------------------------------------------------------------------
    |
    | You can set bayes default settings here. For example which method uses
    | when bayes processing and searching from db.
    |
    | Supported (search.method): "exact_match", "full_text",
    | Supported (search.split): "text", "sentence", "word",
    | Supported (search.full_text.mode): "IN NATURAL LANGUAGE MODE", "IN BOOLEAN MODE",
    |
    */

    'subjective' => array(
        'ham' => 'ham',
        'spam' => 'spam',
    ),

    'objective' => array(
        'natural' => 'natural',
    ),

    'methods' => array(
        'exact_match' => 'Exact Match',
        'full_text' => 'Full Text',
    ),

    'splits' => array(
        'text' => 'Text',
        'sentence' => 'Sentence',
        'word' => 'Word',
    ),

    'search' => array(
        'method' => 'exact_match',
        'split' => 'text',
        'n-gram' => '1-gram',
        'full_text' => array(
            'mode' => 'IN NATURAL LANGUAGE MODE',
//            'mode' => 'IN BOOLEAN MODE',
        ),
    ),


    /*
    |--------------------------------------------------------------------------
    | Text Split Method
    |--------------------------------------------------------------------------
    |
    | You can set bayes default settings here. For example which method uses
    | when bayes processing and searching from db.
    |
    | Supported: "0", "sentence", "word",
    |
    */

    'n-grams' => array(
        '1-gram' => 'Unigram', // 1-gram sequence
        '2-gram' => 'Bigram', // 2-gram sequence
        '3-gram' => 'Trigram', // 3-gram sequence
        '4-gram' => 'Fourgram', // 4-gram sequence
        '5-gram' => 'Fivegram', // 5-gram sequence
        '6-gram' => 'Sixgram', // 6-gram sequence
        '7-gram' => 'Sevengram', // 7-gram sequence
        '8-gram' => 'Eightgram', // 8-gram sequence
        '9-gram' => 'Ninegram', // 9-gram sequence
    ),

);
