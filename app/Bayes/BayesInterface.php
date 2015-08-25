<?php namespace Bayes;

/**
 * Interface BayesInterface
 * @package Bayes
 */
interface BayesInterface {

    /**
     * Train the review.
     * 
     * @param $text
     * @param $tag
     * @return mixed
     */
    public function train($text, $tag);

    /**
     * Re-train an existing review.
     * 
     * @param $id
     * @param $text
     * @param $tag
     * @return mixed
     */
    public function reTrain($id, $text, $tag);

    /**
     * De-train a review.
     * 
     * @param $id
     * @return mixed
     */
    public function deTrain($id);

    /**
     * Multi review train.
     * 
     * @param $data
     * @return mixed
     */
    public function multiTrain($data);

    /**
     * Naive Bayes classification.
     * 
     * @param $text
     * @return mixed
     */
    public function classify($text);

    /**
     * N-grams
     * 
     * @param $text
     * @param $gram
     * @return mixed
     */
    public function nGram($text, $gram);
}