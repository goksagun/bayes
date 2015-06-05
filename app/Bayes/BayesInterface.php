<?php namespace Bayes;

/**
 * Interface BayesInterface
 * @package Bayes
 */
interface BayesInterface {

    /**
     * @param $text
     * @param $tag
     * @return mixed
     */
    public function train($text, $tag);

    /**
     * @param $id
     * @param $text
     * @param $tag
     * @return mixed
     */
    public function reTrain($id, $text, $tag);

    /**
     * @param $id
     * @return mixed
     */
    public function deTrain($id);

    /**
     * @param $data
     * @return mixed
     */
    public function multiTrain($data);

    /**
     * @param $text
     * @return mixed
     */
    public function classify($text);

    /**
     * @param $text
     * @param $gram
     * @return mixed
     */
    public function nGram($text, $gram);
}