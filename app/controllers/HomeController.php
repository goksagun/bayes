<?php

use Faker\Factory;

ini_set('max_execution_time', 0);

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

    /**
     * @return \Illuminate\View\View
     */
    public function getIndex()
	{
//		return Bayes::train($text, $tag);

//        $text = 'Ullam fugit quo qui ut sint itaque doloribus. Libero odio cupiditate amet nemo atque a necessitatibus. Debitis quo minus minus ea nihil. Reiciendis dolorem quibusdam incidunt nesciunt.';
//
//        dd(str_word_count($text, 1));
//
//        $result = $this->nGram($text);
//
//
//        dd($result);

//        $result = preg_split('/\W+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
//
//        return $result;

//        return Review::whereText($text)->count();

//        $result = Bayes::classify($text);
//
//        return $result;

//        dd(Bayes::nGram($text, 1));

//        return Bayes::nGram($text, 1);

//        $tags = Review::tags();

        $subjective = Config::get('bayes.subjective');
        $objective = Config::get('bayes.objective');

        $tags = array_merge($subjective, $objective);

        $nGrams = array_add(Config::get('bayes.n-grams'), '0', 'Select n-Gram');

        ksort($nGrams);

//        dd($tags);

        return View::make('home.index', compact('tags', 'nGrams'));
	}

    public function postTrain()
    {
        $input = Input::all();

        try {
            if (Bayes::train($input['text'], $input['tag'])) {
                if (Request::ajax()) {
                    return Response::json(
                        [
                            'success' => true,
                            'alert' => [
                                'type' => 'success',
                                'message' => "Train successful.",
                            ]
                        ]
                    );
                }
                return Redirect::back()->with('success', "Train successful.");
            }
            return Redirect::back()->with('error', "An error occurred, please try again later.");
        } catch (Exception $e) {
            if (Request::ajax()) {
                return Response::json(
                    [
                        'success' => false,
                        'alert' => [
                            'type' => 'error',
                            'message' => $e->getMessage(),
                        ]
                    ]
                );
            }
            return Redirect::back()->with('error', $e->getMessage());
        }
    }

    public function postClassify()
    {
        $input = Input::all();

        try {
            $data = Bayes::classify($input['text']);
            if (Request::ajax()) {
                return Response::json(
                    [
                        'success' => true,
                        'data' => $data,
                    ]
                );
            }
            return Redirect::back()->with('success', "Train successful.");
//            return Redirect::back()->with('error', "An error occurred, please try again later.");
        } catch (Exception $e) {
            if (Request::ajax()) {
                return Response::json(
                    [
                        'success' => false,
                        'alert' => [
                            'type' => 'error',
                            'message' => $e->getMessage(),
                        ]
                    ]
                );
            }
            return Redirect::back()->with('error', $e->getMessage());
        }
    }

    /**
     * @param $text
     * @return array
     */
    protected function nGram($text)
    {
        $re = '/\W+/';
        $words = preg_split($re, strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        $nGram = [
            '1-gram' => 'unigram', // 1-gram sequence
            '2-gram' => 'bigram', // 2-gram sequence
            '3-gram' => 'trigram', // 3-gram sequence
            '4-gram' => 'fourgram', // 4-gram sequence
            '5-gram' => 'fivegram', // 5-gram sequence
            '6-gram' => 'sixgram', // 6-gram sequence
            '7-gram' => 'sevengram', // 7-gram sequence
            '8-gram' => 'eightgram', // 8-gram sequence
            '9-gram' => 'ninegram', // 9-gram sequence
        ];

        $nGramMethod = 'trigram';
        $nGramMethodKey = array_keys($nGram, $nGramMethod);
        $nGramMethodKeyString = current($nGramMethodKey);
        $n = (int)strstr($nGramMethodKeyString, '-', true);

        $data = [];

        if ($n > 1) {
            $i = 0;
            while ($i <= ($wordCount - $n)) {
                $j = $i;
                $tempArray = [];
                while ($j < ($n + $i)) {
                    $tempArray[] = $words[$j];

                    $j++;
                }

                $data[] = implode(' ', $tempArray);

                $i++;
            };
        } else {
            $data = $words;
        }

        return array(
            'words' => $words,
            'n' => $n,
            'data' => $data
        );
    }
}
