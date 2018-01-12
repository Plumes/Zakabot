@extends('amp_layout')
@section('head')
    <meta charset="utf-8">
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <title>乃木坂46 公式ブログ</title>
    <link rel="canonical" href="http://blog.nogizaka46.com/">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
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
            display: inline-block;
        }
        div.meta {
            display: inline-block;
            height: 60px;
            margin-top: 10px;
            vertical-align: top;
            margin-left: 15px;
        }
        .author-name, .post-date {
            display: inline-block;
            font-size: 1.5em;
            color: #333333;
            height: 30px;
            line-height: 30px;
        }
        .title {
            font-size: 16px;
            color: #777777;
            height: 30px;
            line-height: 30px;
        }
        div.content .meta,div.content .profile-pic {
            margin: 0 0 20px 0;
        }
        div.content .author-name  {
            font-size: 14px;
            color: #777777;
        }
        div.content .post-date {
            font-size: 14px;
            color: #777777;
            margin-left: 10px;
        }
        div.content .title {
            font-size: 18px;
            color: #333333;
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
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <script type="application/ld+json"><?php echo json_encode($schema,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?></script>
@endsection

@section('body')
<header class="header">
    <div class="header-title">
        <div class="profile-pic">
            <amp-img alt="乃木坂46"
                     src="{{ $logo }}"
                     class="contain"
                     layout="fill">
            </amp-img>
        </div>
        <div class="meta">
            <div class="author-name">乃木坂46</div>
            <div class="title">Member Blog</div>
        </div>
    </div>
</header>
@foreach($posts as $post)
<article>
    <div class="content">
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
    <div>{!! $post->content !!}</div>
        <a href="{!! $post->inner_url !!}" class="readmore">阅读全文</a>
    </div>
</article>
@endforeach
@endsection