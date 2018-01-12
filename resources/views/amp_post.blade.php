<!doctype html>
<html ⚡>
<head>
    <meta charset="utf-8">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <title>{{ $member->name }} - {{ $post->abbr_title }}</title>
    <link rel="canonical" href="{!! $post->url !!}">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <style amp-custom>
        .header {
            position: absolute;
            width: 100%;
            height: 80px;
            padding: 0 25px;
            background-color: #fff;
            box-shadow: 0 0 40px 0 rgba(0,0,0,0.1);
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
            padding: 105px 15px 0;
            background: #7e1083;
        }
        article div.content {
            padding: 0 10px;
            background-color: #f8f8f8;;
        }
    </style>
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
</head>
<body>
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
</body>
</html>