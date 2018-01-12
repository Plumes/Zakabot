<!doctype html>
<html ⚡>
<head>
    @yield('head')
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