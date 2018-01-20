@extends('ngzk_amp_layout')

@section('title',$member->name."-".$post->abbr_title)
@section('canonical',url("/amp/nogizaka46/".$member->id."/".$post->id))

@section('custom-style')
        .title {
            font-size: 1.5rem;
            color: #333333;
            line-height: 30px;
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
        div.entrybody p {
            height: auto;
        }
@endsection

@section('body')
<header class="header">
    <div class="header-title">
        <div class="logo">
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
