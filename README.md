# signature_rotator
A simple PHP signature rotator that supports imagick and gd libraries.

Config Details:
$err					Error reporting level.
$comp					Compositor class location.
$randStorage			Previous random value storage location. Will be adding database options soon.
$numStorage				Generation number. Only relevant during composition, and only as a tracking tool. Will be adding database options soon.
$userQ					Query string index to look for that identifies which "user" or folder to get a random signature from.
$modeQ					Query string index to look for that identifies which "mode" to run the script in. Currently only signature is supported, but a management tool is in the works.
$imgQ					Query string index to look for that identifies which image to load. This allows singular specific images to be loaded.
$imgDir					The path, from the $userQ folder, to the image directory.
$outDir					The path, from the $userQ folder, to the cache directory.
$datDir					The path, from the $userQ folder, to the data directory. This is only used in the compositor.
$referAllowed			Boolean saying whether or not external referals are allowed.
$referList				An array containing allowed referers. The machine host is always allowed.
$indepthRefererScan		Boolean saying whether to do an indepth scan of the referer using containing instead of a literal scan of the referer.
$debugger				Boolean saying whether to print debugging information on direct calls of the image.
$caching				Which caching mode to send with the headers.

Usage:
WIP