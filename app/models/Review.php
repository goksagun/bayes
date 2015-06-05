<?php

class Review extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reviews';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $fillable = ['text', 'tag'];

    /**
     * Get all tags
     *
     * @return array
     */
    public function scopeTags()
    {
        return $this->groupBy('tag')->lists('tag', 'tag');
    }

    /**
     * Get all tags count
     *
     * @return array
     */
    public function scopeTagCount()
    {
        return $this->selectRaw('count(*) as tag_count, tag')
            ->groupBy('tag')
            ->get();
    }

    /**
     * Search exact match
     *
     * @return array
     */
    public function scopeSearch($query, $param)
    {
        return $query->selectRaw('count(*) as tag_count, tag, text')
            ->whereText($param)
            ->groupBy('tag')
            ->get();
    }

    /**
     * Search full text
     *
     * @return array
     */
    public function scopeFullTextSearch($query, $match, $param)
    {
        return $query->selectRaw('count(*) as tag_count, tag, text')
            ->whereRaw($match, [$param])
            ->groupBy('tag')
            ->get();
    }
}