@extends('amp_layout')

@section('title','乃木坂46 公式ブログ')
@section('canonical','http://blog.nogizaka46.com/')
@section('head')
    <style amp-custom>
        body {
            background: #7e1083;
        }
        .header {
            width: 100%;
            height: 80px;
            padding: 0 25px;
            background-color: #fff;
            box-shadow: 0 0 40px 0 rgba(0,0,0,0.5);
            font-size: 14px;
        }
        .header .logo {
            margin-top: 10px;
            width: 60px;
            height: 60px;
            position: relative;
            background-color: rgb(241,241,243);
            border-radius: 30px;
            overflow: hidden;
            display: inline-block;
        }
        .header .site-meta {
            display: inline-block;
            height: 60px;
            margin-top: 10px;
            vertical-align: top;
            margin-left: 15px;
        }
        .header .site-name {
            font-size: 1.5em;
            color: #333333;
            height: 30px;
            line-height: 30px;
        }
        .header .site-desc {
            font-size: 16px;
            color: #777777;
            height: 30px;
            line-height: 30px;
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
        amp-img.contain img {
            object-fit: contain;
        }
        .fixed-height-container {
            position: relative;
            width: 100%;
            height: 300px;
        }
        article {
            display: block;
            margin: 30px 15px 30px 15px;
            padding: 20px 10px;
            background-color: #f8f8f8;
            box-shadow: 0 5px 40px 0 rgba(0,0,0,0.5);
            border-radius: 2px;
            box-sizing: border-box;
            position: relative;
        }
        article .article-head {
            position: relative;
            height: 60px;
            margin-bottom: 20px;
        }
        article .profile-pic {
            width: 60px;
            height: 60px;
            position: relative;
            background-color: rgb(241,241,243);
            border-radius: 30px;
            overflow: hidden;
            float: left;
        }
        article .meta {
            width: 100%;
            padding-left: 70px;
            box-sizing: border-box;
        }
        article .meta .author-name {
            height: 30px;
            line-height: 30px;
            font-size: 14px;
            color: #777777;
            display: inline-block;
        }
        article .meta .post-date {
            height: 30px;
            line-height: 30px;
            display: inline-block;
            font-size: 12px;
            color: #777777;
            margin-left: 10px;
        }
        article .meta .title {
            height: 30px;
            line-height: 30px;
            font-size: 18px;
            color: #333333;
            overflow: hidden;
            white-space: nowrap;
            text-overflow:ellipsis;
        }
        article div.content {

        }
        footer {
            padding: 0 15px 20px 15px;
            display: inline-block;
            width: 100%;
            background-color: #7e1083;;
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
        .readmore {
            font-size: 16px;
            padding: 5px 10px;
            border-radius: 3px;
            border: 1px solid #7e1083;
            color: #7e1083;
            display: inline-block;
            text-decoration: none;
            margin-top: 20px;
        }
        a.readmore:visited {
            text-decoration: none;
            color: #7e1083;
        }
    </style>
@endsection

@section('body')
<header class="header">
    <div class="header-title">
        <div class="logo">
            <amp-img alt="乃木坂46"
                     src="{{ $logo }}"
                     class="contain"
                     layout="fill">
            </amp-img>
        </div>
        <div class="site-meta">
            <div class="site-name">乃木坂46</div>
            <div class="site-desc">Member Blog</div>
        </div>
    </div>
</header>
@foreach($posts as $post)
<article>
    <div class="article-head">
    <div class="profile-pic">
        <amp-img alt="{{ $post->name }}"
                 src="{{ $post->profile_pic }}"
                 class="contain"
                 layout="fill">
        </amp-img>
    </div>
    <div class="meta">
        <div class="author-name">{{ $post->name }}</div>
        <div class="post-date">
            {{ $post->posted_at }}
        </div>
        <div class="title">
            {{ $post->title }}
        </div>
    </div>
    </div>
    <div class="content">{!! $post->content !!}</div>
    <a href="{!! $post->inner_url !!}" class="readmore">阅读全文</a>
</article>
@endforeach
@endsection