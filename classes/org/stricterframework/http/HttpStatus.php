<?php

interface HttpStatus
{
	const HTTP_100="HTTP/1.1 100 Continue";
	const HTTP_101="HTTP/1.1 101 Switching Protocols";
	const HTTP_103="HTTP/1.1 103 Checkpoint";

	const HTTP_200="HTTP/1.1 200 OK";
	const HTTP_201="HTTP/1.1 201 Created";
	const HTTP_202="HTTP/1.1 202 Accepted";
	const HTTP_203="HTTP/1.1 203 Non-Authoritative Information";
	const HTTP_204="HTTP/1.1 204 No Content";
	const HTTP_205="HTTP/1.1 205 Reset Content";
	const HTTP_206="HTTP/1.1 206 Partial Content";

	const HTTP_300="HTTP/1.1 300 Multiple Choices";
	const HTTP_301="HTTP/1.1 301 Moved Permanently";
	const HTTP_302="HTTP/1.1 302 Found";
	const HTTP_303="HTTP/1.1 303 See Other";
	const HTTP_304="HTTP/1.1 304 Not Modified";
	const HTTP_306="HTTP/1.1 306 Switch Proxy";
	const HTTP_307="HTTP/1.1 307 Temporary Redirect";
	const HTTP_308="HTTP/1.1 308 Resume Incomplete";

	const HTTP_400="HTTP/1.1 400 Bad Request";
	const HTTP_401="HTTP/1.1 401 Unauthorized";
	const HTTP_402="HTTP/1.1 402 Payment Required";
	const HTTP_403="HTTP/1.1 403 Forbidden";
	const HTTP_404="HTTP/1.1 404 Not Found";
	const HTTP_405="HTTP/1.1 405 Method Not Allowed";
	const HTTP_406="HTTP/1.1 406 Not Acceptable";
	const HTTP_407="HTTP/1.1 407 Proxy Authentication Required";
	const HTTP_408="HTTP/1.1 408 Request Timeout";
	const HTTP_409="HTTP/1.1 409 Conflict";
	const HTTP_410="HTTP/1.1 410 Gone";
	const HTTP_411="HTTP/1.1 411 Length Required";
	const HTTP_412="HTTP/1.1 412 Precondition Failed";
	const HTTP_413="HTTP/1.1 413 Request Entity Too Large";
	const HTTP_414="HTTP/1.1 414 Request-URI Too Long";
	const HTTP_415="HTTP/1.1 415 Unsupported Media Type";
	const HTTP_416="HTTP/1.1 416 Requested Range Not Satisfiable";
	const HTTP_417="HTTP/1.1 417 Expectation Failed";

	const HTTP_500="HTTP/1.1 500 Internal Server Error";
	const HTTP_501="HTTP/1.1 501 Not Implemented";
	const HTTP_502="HTTP/1.1 502 Bad Gateway";
	const HTTP_503="HTTP/1.1 503 Service Unavailable";
	const HTTP_504="HTTP/1.1 504 Gateway Timeout";
	const HTTP_505="HTTP/1.1 505 HTTP Version Not Supported";
	const HTTP_511="HTTP/1.1 511 Network Authentication Required";

}

?>
