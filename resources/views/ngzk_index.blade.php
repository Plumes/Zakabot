@extends('ngzk_amp_layout')

@section('title','乃木坂46 公式ブログ')
@section('canonical','http://blog.nogizaka46.com/')
@section('custom-style')

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
    <div class="content">{!! $post->preview !!} ......</div>
    <a href="{!! $post->inner_url !!}" class="readmore">阅读全文</a>
</article>
@endforeach
<footer>
    @if($current_page>1)
        <a href="{!! url("/amp/nogizaka46/?page=".($current_page-1)) !!}" class="prev">PREV</a>
    @endif

    @if($has_more)
        <a href="{!! url("/amp/nogizaka46/?page=".($current_page+1)) !!}" class="next">NEXT</a>
    @endif
</footer>
@endsection