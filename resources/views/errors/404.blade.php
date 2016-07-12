<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
		{!! HTML::style('css/frozen.css') !!}
		{!! HTML::style('css/style.css') !!}
		<title>404</title>
	</head>
	<body>
		<div class="content">
      <section class="ui-notice">
        <i></i>
				@if (count($errors) > 0)
				    <div class="alert alert-danger">
				        <ul>
				            @foreach ($errors->all() as $error)
				                <li>{{ $error }}</li>
				            @endforeach
				        </ul>
				    </div>
				@else
					<p>请求参数有误</p>
				@endif
      </section>
		</div>
	</body>
</html>
