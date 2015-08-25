<?php namespace Bayes;

use Carbon\Carbon;
use Config;
use Exception;
use Review;
use Input;

/**
 * Class Bayes
 * 
 * @package Bayes
 */
class Bayes implements BayesInterface {

    /**
     * Maximum data store.
     * 
     * @var int
     */
    protected $maxInsert = 1000;

    /**
     * Train method excepts any text.
     * 
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
     * Retrain existing text in database reviews.
     * 
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
     * Rollback the training a review. This method deletes the review from database.
     * 
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
     * For multi review training.
     * 
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
     * Many natural language processing tasks require classification, you want to find out to which class 
     * a particular instance belongs. To make this more concrete, we give three examples:
     *
     * - Authorship attribution: suppose that you were given a text, and have to pick the correct author of 
     * the text from three proposed authors. 
     * 
     * - Part of speech tagging: in part of speech tagging, words are 
     * classified morphosyntactically. For instance, we could classify the word 'loves' in the statement 
     * "John loves Mary" to be a verb. 
     * 
     * - Fluency ranking: in natural language generation, we want to find 
     * out whether a sentence produced by a generation system is fluent or not fluent.
     *
     * If we know the expected value from the observation of repeated coin flips (the training data), 
     * we can make a model that gives the same outcome. If we know the payments, finding the model 
     * analytically is trivial. What if we do the same for features? We can calculate the feature 
     * value in the training data:
     *
     * 1- Calculating the empirical value of a feature
     * E p̃ [ f i ] = ∑ x,y p̃(x,y) fi (x,y)
     *
     * It's easier than it looks: the empirical value of a feature fi is the sum of the multiplication 
     * joint probability of a context and an event in the training data and the value of fi for that 
     * context and event. We can also calculate the expected value of a feature fi according to the 
     * conditional model p(y|x):
     * 
     * 2- Calculating the expected value of a feature
     * E p [ f i ] = ∑ x,y p̃(x) p(y|x) fi (x,y)
     *
     * Since p(x,y) ≡ p(x) p(y|x) , and the model only estimates the conditional probability p(y|x), 
     * the probability of the context in the training data, p̃(x) , is used. To make the model predict 
     * the training data, a constraint is added for each feature fi during the training of the model, 
     * such that:
     * 
     * 3- Constraining the expected value to the empirical value:
     * E p̃ [ f i ] = E p [ f i ]
     * 
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
     * An N-gram is an N-character slice of a longer string. Although in the literature the term can include 
     * the notion of any co-occurring set of characters in a string (e.g., an N-gram made up of the first and 
     * third character of a word), in this paper we use the term for contiguous slices only.
     * 
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

                    foreach ($texts as $text) {
                        $result[] = $this->_getResult($text, $method, $split, $full_text_mode);
                    }

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

                        foreach ($texts as $text) {
                            $queryText[] = sprintf('"%s"', $text);
//                            $result[] = $this->_getResult($text, $method, $split, $full_text_mode);
                        }

                        $query = implode(' ', $queryText);
//                        dd($query);
                        $result[] = $this->_getResult($query, $method, $split, $full_text_mode);

                    }
                    else {
                        $result[] = $this->_getResult($sentence, $method, $split, $full_text_mode);
                    }
                }
                break;
            case 'word':
                $re = '/\W+/';
                $words = preg_split($re, strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $result[] = $this->_getResult($word, $method, $split, $full_text_mode);
                }
                break;
        }

        return $result;
    }

    /**
     * Check vars.
     * 
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
     * Check text if empty throw an exception.
     * 
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
     * Check tag if empty throw an exception.
     * 
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

    /**
     * Select full-text mode and return result.
     * 
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
     * An N-gram is an N-character slice of a longer string. Although in the literature the term can include 
     * the notion of any co-occurring set of characters in a string (e.g., an N-gram made up of the first and 
     * third character of a word), in this paper we use the term for contiguous slices only.
     *
     * Typically, one slices the string into a set of overlapping N-grams. In our system, we use N-grams of 
     * several different lengths simultaneously. We also append blanks to the beginning and ending of the string 
     * in order to help with matching beginning-of-word and ending-of-word situations. (We will use the underscore 
     * character (“_”) to represent blanks.) Thus, the word “TEXT” would be composed of the following N-grams:
     *
     *  - bi-grams: _T, TE, EX, XT, T_ 
     *  - tri-grams: _TE, TEX, EXT, XT_, T_ _ 
     *  - quad-grams: _TEX, TEXT, EXT_, XT_ _, T_ _ _ 
     *  
     * In general, a string of length k, padded with blanks, will have k+1 bi-grams, k+1tri-grams, k+1 quad-grams, 
     * and so on.
     * 
     * @param $text
     * @param $nGram
     * @return array|mixed
     */
    private function _nGramMethod($text, $nGram)
    {
//        $re = '/\W+/';
//        $words = preg_split($re, strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text, 1);
        $wordCount = str_word_count($text);

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

        if ($wordCount > $n && $n > 1) {
            $i = 0;
            while ($i <= ($wordCount - $n)) {
                $j = $i;
                $tempArray = [];
                while ($j < ($n + $i)) {
                    $tempArray[] = $words[$j];

                    $j++;
                }

                $result[] = implode(' ', $tempArray);

                $i++;
            };
        } else {
            $result = $words;
        }

        return $result;
    }
}