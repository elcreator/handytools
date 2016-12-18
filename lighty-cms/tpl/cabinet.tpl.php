<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <meta name="Description" content="<?=$Description?>" /> 
  <meta name="Keywords" content="<?=$Keywords?>" /> 
  <title><?=$Title?></title>
  <link href="/css/common.css<?=$NoCache?>" rel="stylesheet" type="text/css" />
  <!--[if gte IE 9]><link href="/css/ie9.css<?=$NoCache?>" rel="stylesheet" type="text/css" /><![endif]-->
  <!--[if IE 8]><link href="/css/ie8.css<?=$NoCache?>" rel="stylesheet" type="text/css" /><![endif]-->
  <!--[if IE 7]><link href="/css/ie7.css<?=$NoCache?>" rel="stylesheet" type="text/css" /><![endif]-->
  <!--[if lte IE 6]><link href="/css/ie6.css<?=$NoCache?>" rel="stylesheet" type="text/css" /><![endif]-->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js" type="text/javascript"></script>
</head>

<body>

<div class="container">
<?=$Content?>
</div>

<script src="/js/common.js<?=$NoCache?>" type="text/javascript"></script>

<script type="text/javascript">
<?=$InlineScript?>
</script>

</body>
</html>