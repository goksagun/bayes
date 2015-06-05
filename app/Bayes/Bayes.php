<?php namespace Bayes;

use Carbon\Carbon;
use Config;
use Exception;
use Review;
use Input;

/**
 * Class Bayes
 * @package Bayes
 */
class Bayes implements BayesInterface {

    /**
     * @var int
     */
    protected $maxInsert = 1000;

    /**
     * @param $text
     * @param $tag
     * @return string
     * @throws Exception
     */
    public function train($text, $tag)
    {
        $input = $this->_checkVars($text, $tag);

        if (Review::whereText($input['text'])->exists()) {
            throw new Exception("Review already exist.");
        }

        return Review::create(
            array('text' => $input['text'], 'tag' => $input['tag'])
        );
    }

    /**
     * @param $id
     * @param $text
     * @param $tag
     * @return bool
     * @throws Exception
     */
    public function reTrain($id, $text, $tag)
    {
        $review = Review::find($id);

        if (is_null($review)) {
            throw new Exception("Review not found.");
        }

        $input = $this->_checkVars($text, $tag);

        $review->text = $input['text'];
        $review->tag = $input['tag'];

        return $review->save();
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    public function deTrain($id)
    {
        $review = Review::find($id);

        if (is_null($review)) {
            throw new Exception("Review not found.");
        }

        return $review->delete();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function multiTrain($data)
    {
        foreach ($data as $key => $value) {
            if ( !isset($row['created_at'])) {
                $data[$key]['created_at'] = Carbon::now('Europe/Istanbul');
            }
            if ( !isset($row['updated_at'])) {
                $data[$key]['updated_at'] = Carbon::now('Europe/Istanbul');
            }
        }

        if (count($data) >= $this->maxInsert) {
            $data = array_chunk($data, $this->maxInsert);

            $inserted = 0;
            foreach ($data as $insert) {
                if (Review::insert($insert)) $inserted += $this->maxInsert;
            }
        }
        else {
            $data = array_chunk($data, 1000);

            $inserted = Review::insert($data);
        }

        return $inserted;
    }

    /**
     * @param $text
     * @return string
     */
    public function classify($text)
    {
        $text = $this->_checkText($text);

        $_start = microtime(true);
        // get all tags
        $tags = Review::tagCount();

        $tagCount = [];
        foreach ($tags as $tag) {
            $tagCount[$tag->tag] = $tag->tag_count;
        }

//        return $tagCount;

        $texts = $this->nGram($text, true);

//        return $texts;

        if (count($texts)) {
            $textTagCount = [];
            foreach ($texts as $text) {
                foreach ($text['data'] as $text) {
                    $textTagCount[$text->tag] = (int)$text->tag_count;
                }
            }

//            P ˆ (ω = spam) = # of spam msg. / # of all msg.
//            P ˆ (ω = ham) = # of ham msg. / of all msg.

            // calculate bayes rate
            foreach ($textTagCount as $key => $value) {
//                $result[$key] = $value / $tagCount[$key];
                $result[$key] = $value / array_sum($tagCount);
            }

            // set data
            foreach ($result as $key => $value) {
                $data['data'][$key] = [
                    'count' => $textTagCount[$key],
                    'rate' => $value,
                    'percent' => round($value * 100 / array_sum($result)),
                ];
            }

            $data['probability'] = current(array_keys($result, max($result)));
        }
        else {
            $data['probability'] = 'No result.';
        }
        $_end = microtime(true);

        $data['time_elapsed'] = $_end - $_start;

//        return $textTagCount;

        return $data;
    }

    /**
     * @param $text
     * @param bool $nGram
     * @return array
     */
    public function nGram($text, $nGram = false)
    {
        $result = [];
        $method = Input::has('method') ? Input::get('method') : Config::get('bayes.search.method', 'exact_match');
        $split = Input::has('split') ? Input::get('split') : Config::get('bayes.search.split', 'text');
        if ($nGram) {
            $nGram = Input::has('n-gram') ? Input::get('n-gram') : Config::get('bayes.search.n-gram', 'Unigram');
        }
        $full_text_mode = Config::get('bayes.search.full_text.mode', 'IN NATURAL LANGUAGE MODE');

        switch ($split) {
            case 'text':
                if ($nGram) {
                    $texts = $this->_nGramMethod($text, $nGram);

//                    foreach ($texts as $text) {
//                        $result[] = $this->_getResult($text, $method, $split, $full_text_mode);
//                    }
                    $result[] = $this->_getResult($texts, $method, $split, $full_text_mode);

                }
                else {
                    $result[] = $this->_getResult($text, $method, $split, $full_text_mode);
                }
                break;
            case 'sentence':
                $re = '/                    # Split sentences on whitespace between them.
                        (?<=                # Begin positive lookbehind.
                          [.!?]             # Either an end of sentence punct,
                        | [.!?][\'"]        # or end of sentence punct and quote.
                        )                   # End negative lookbehind.
                        \s+                 # Split on whitespace between sentences.
                        /ix';
                $sentences = preg_split($re, $text, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($sentences as $sentence) {
                    if ($nGram) {
                        $texts = $this->_nGramMethod($sentence, $nGram);

//                        foreach ($texts as $text) {
////                            $queryText[] = sprintf('"%s"', $text);
//                            $result[] = $this->_getResult($text, $method, $split, $full_text_mode);
//                        }

//                        $query = implode(' ', $queryText);
//                        dd($query);
                        $result[] = $this->_getResult($texts, $method, $split, $full_text_mode);

                    }
                    else {
                        $result[] = $this->_getResult($sentence, $method, $split, $full_text_mode);
                    }
                }
                break;
            case 'word':
                $words = preg_split('/\W+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $result[] = $this->_getResult($word, $method, $split, $full_text_mode);
                }
                break;
        }

        return $result;
    }

    /**
     * @param $text
     * @param $nGram
     * @return array|mixed
     */
    private function _nGramMethod($text, $nGram)
    {
        $sanitized = preg_replace('/[[:punct:]]/', '', $text);

//        dd($sanitized);
        $words = preg_split('/\W+/', strtolower($sanitized), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
//        $words = str_word_count($text, 1);
//        $wordCount = str_word_count($text);

//        dd($words, $wordCount);

        $nGrams = Config::get('bayes.n-grams');

        // starts with a number like 1-gram
        if (preg_match('/^\d/', $nGram) === 1) {
            $nGramMethodKeyString = $nGram;
        } else {
            $nGramMethodKey = array_keys($nGrams, $nGram);
            $nGramMethodKeyString = current($nGramMethodKey);
        }
        $n = (int)strstr($nGramMethodKeyString, '-', true);

        $result = [];

        if ($wordCount > ($n - 1) && $n > 1) {
            for ($i = 0; $i < ($wordCount - ($n - 1)); $i++) {
                $tempArray = [];
                for ($j = $i; $j < ($i + $n); $j++) {
                    $tempArray[] = $words[$j];
                }
                $result[] = implode(' ', $tempArray);
            }
        } else {
            $result = $text;
        }

        return $result;
    }

    /**
     * @param $str
     * @param $method
     * @param $split
     * @param null $full_text_mode
     * @return array
     */
    private function _getResult($str, $method, $split, $full_text_mode = null)
    {
        if (is_null($full_text_mode)) {
            $match = 'match (text) against (?)';
        } else {
            $match = 'match (text) against (? ' . $full_text_mode . ')';
        }

        switch ($method) {
            case 'exact_match':
                $data = Review::search($str);
                break;
            case 'full_text':
                if (is_array($str)) {
                    foreach ($str as $text) {
                        $queryText[] = sprintf('"%s"', $text);
                    }
                    $str = implode(' ', $queryText);
                }
//                dd($str);
                $data = Review::fullTextSearch($match, $str);
                break;
        }

        $result = [
            'method' => $method,
            'type' => $split,
            'string' => $str,
            'data' => $data,
        ];

        return $result;
    }

    /**
     * @param $text
     * @param $tag
     * @return array
     * @throws Exception
     */
    private function _checkVars($text, $tag)
    {
        $text = $this->_checkText($text);

        $tag = $this->_checkTag($tag);

        return compact('text', 'tag');
    }

    /**
     * @param $text
     * @return string
     * @throws Exception
     */
    private function _checkText($text)
    {
        $text = trim($text);

        if (is_null($text)) {
            throw new Exception("The text is not to be null.");
        }

        if ($text == '') {
            throw new Exception("The text is not be empty string.");
        }

        return $text;
    }

    /**
     * @param $tag
     * @return string
     * @return string
     * @throws Exception
     */
    private function _checkTag($tag)
    {
        $tag = trim($tag);

        if (is_null($tag)) {
            throw new Exception("The tag is not to be null.");
        }

        if ($tag == '') {
            throw new Exception("The tag is not be empty string.");
        }

        return $tag;
    }
}