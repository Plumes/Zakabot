<!doctype html>
<html ⚡>
<head>
    <meta charset="utf-8">
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <title>@yield('title')</title>
    <link rel="canonical" href="@yield('canonical')">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,maximum-scale=1.0, user-scalable=no">
    @yield('head')
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <script type="application/ld+json"><?php echo json_encode($schema,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?></script>
</head>
<body>
    @yield('body')
</body>
<amp-analytics type="googleanalytics" id="analytics1">
    <script type="application/json">
{
  "vars": {
    "account": "UA-52774518-3"
  },
  "triggers": {
    "trackPageview": {
      "on": "visible",
      "request": "pageview"
    }
  }
}
</script>
</amp-analytics>
</html>