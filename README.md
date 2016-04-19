# signature_rotator
A simple PHP signature rotator that supports imagick and gd libraries.

Config Details:

$err					Error reporting level.<br />
$comp					Compositor class location.<br />
$randStorage			Previous random value storage location. Will be adding database options soon.<br />
$numStorage				Generation number. Only relevant during composition, and only as a tracking tool. Will be adding database options soon.<br />
$userQ					Query string index to look for that identifies which "user" or folder to get a random signature from.<br />
$modeQ					Query string index to look for that identifies which "mode" to run the script in. Currently only signature is supported, but a management tool is in the works.<br />
$imgQ					Query string index to look for that identifies which image to load. This allows singular specific images to be loaded.<br />
$imgDir					The path, from the $userQ folder, to the image directory.<br />
$outDir					The path, from the $userQ folder, to the cache directory.<br />
$datDir					The path, from the $userQ folder, to the data directory. This is only used in the compositor.<br />
$referAllowed			Boolean saying whether or not external referals are allowed.<br />
$referList				An array containing allowed referers. The machine host is always allowed.<br />
$indepthRefererScan		Boolean saying whether to do an indepth scan of the referer using containing instead of a literal scan of the referer.<br />
$debugger				Boolean saying whether to print debugging information on direct calls of the image.<br />
$caching				Which caching mode to send with the headers.<br />

Usage:

WIP