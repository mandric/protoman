<?php


class Tag extends Saveable
{
    protected $name = array('CharField', 'label' => 'Name');
    
    public function toString()
    {
        return $this->name->get();
    }
}


class Post extends Saveable
{
    protected $title = array('CharField', 'label' => 'Title');
    protected $summary = array('TextField', 'label' => 'Summary');
    protected $body = array('HtmlField', 'label' => 'Body');
    
    protected $tags = array('ManyToManyField', 'Tag', 'label' => 'Tags');
    protected $comments = array('ManyToManyField', 'comment', 'label' => 'Comments');
    
    public function toString()
    {
        return $this->title->get();
    }
}


class Comment extends Saveable
{
    protected $name = array('CharField', 'label' => 'Name');
    protected $body = array('TextField', 'label' => 'Body');
}
