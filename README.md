PHP parser for Adblock Plus filters
===================================

Usage
-----

To learn about Adblock Plus filter syntax check these links:

* https://adblockplus.org/en/filter-cheatsheet
* https://adblockplus.org/en/filters

1. Get filter rules somewhere: write them manually, read lines from a file
   downloaded from [EasyList](https://easylist.to/), etc.:

   ```php
   $rules = [
       "||ads.example.com^",
       "@@||ads.example.com/notbanner^$~script",
   ];
   ```

2. Create AdblockRules instance from the rules array:

   ```php
   use Limonte\PhpAdblockParser as AdblockParser;

   $adblockParser = AdblockParser($rules);
   ```

3. Use this instance to check if an URL should be blocked or not:

   ```php
   $adblockParser->shouldBlock("http://ads.example.com"); // true
   $adblockParser->shouldBlock("http://non-ads.example.com"); // false
   ```
