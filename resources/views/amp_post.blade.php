@extends('amp_layout')

@section('title',$member->name."-".$post->abbr_title)
@section('canonical',url("/amp/nogizaka46/".$member->id."/".$post->id))

@section('head')
    <style amp-custom>
        body {
            background-color: #7e1083;;
        }
        .header {
            width: 100%;
            background-color: #fff;
            box-shadow: 0 0 40px 0 rgba(0,0,0,0.5);
            font-size: 14px;
        }
        p {
            height: 1em;
            line-height: 1em;
            margin: 0;
        }
        div.font-size-1 {
            font-size: 14px;
            display: inline;
        }
        div.profile-pic {
            margin-top: 10px;
            width: 60px;
            height: 60px;
            position: relative;
            background-color: rgb(241,241,243);
            border-radius: 30px;
            overflow: hidden;
            float: left;
            margin-left: 15px;
        }
        div.meta {
            vertical-align: top;
            width: 100%;
            padding: 10px 20px 10px 75px;
            box-sizing: border-box;
        }
        .author-name, .post-date {
            display: inline-block;
            font-size: 16px;
            color: #777777;
            height: 30px;
            line-height: 30px;
        }
        .title {
            font-size: 1.5rem;
            color: #333333;
            height: 30px;
            line-height: 30px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow:ellipsis;
        }
        amp-img.contain img {
            object-fit: contain;
        }
        .fixed-height-container {
            position: relative;
            width: 100%;
            height: 300px;
        }
        article {
            padding: 30px 15px 30px 15px;
        }
        article div.content {
            padding: 20px 10px;
            background-color: #f8f8f8;
            box-shadow: 0 5px 40px 0 rgba(0,0,0,0.5);
            border-radius: 2px;
            box-sizing: border-box;
        }
        footer {
            padding: 0 15px 20px 15px;
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
        }
        footer a {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #ffffff;
            text-decoration: none;
            box-shadow: 0 5px 40px 0 rgba(0,0,0,0.5);
            border-radius: 2px;
        }
        footer .prev {
            align-self: flex-start;
        }
        footer .next {
            align-self: flex-end;
            float: right;
        }
    </style>
@endsection

@section('body')
<header class="header">
    <div class="header-title">
        <div class="profile-pic">
            <amp-img alt="{{ $member->name }}"
                     src="{{ $member->profile_pic }}"
                     class="contain"
                    layout="fill">
            </amp-img>
        </div>
        <div class="meta">
        <div class="author-name">{{ $member->name }}</div>
        <div class="post-date">
            {{ $post->posted_at }}
        </div>
        <div class="title">
            {{ $post->title }}
        </div>
        </div>
    </div>
</header>
<article><div class="content">{!! $post->content !!}</div></article>
<footer>
    @if($post->prev)
        <a href="{!! url("/amp/nogizaka46/".$post->member_id."/".$post->prev) !!}" class="prev">PREV</a>
    @endif

    @if($post->next)
        <a href="{!! url("/amp/nogizaka46/".$post->member_id."/".$post->next) !!}" class="next">NEXT</a>
    @endif
</footer>
@endsection
