# Klassen-Referenz

Methode | Erläuterung
-----|-----
[__construct($_clang = false, $_loadSettings = true, $_useStopwords = true)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Class constructor
[generateIndex()](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Generates the full index at once.
[indexArticle($_id,$_clang = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Indexes a certain article.
[unindexArticle($_id,$_clang = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Removes an article from the index.
[indexColumn($_table, $_column, $_idcol = false, $_id = false, $_start = false, $_count = false, $_where = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Indexes a certain column. Returns the number of the indexed rows or false.
[indexFile($_filename, $_doPlaintext = false, $_clang = false, $_fid = false, $_catid = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Indexes a certain file. Returns SEARCH_IT_FILE_GENERATED or an error code.
[indexURL($id, $article_id, $clang_id, $profile_id, $data_id)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Indexes a certain url from URL Addon.
[deleteIndex()](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Deletes the complete search index. [deleteIndex()](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Deletes the complete search index.
[deleteIndexForType($texttype)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Deletes the complete search index for a special text type.
[indexURL($id, $article_id, $clang_id, $profile_id, $data_id)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Indexes a certain url from URL Addon.
[deleteCache($_indexIds = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Truncates the cache or deletes all data that are concerned with the given index-ids.
[deleteKeywords()](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Delete Keywords
-----|-----
[setSearchString($_searchString)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Set search string
[searchInArticles($_ids)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the IDs of the articles which are only to be searched through. Expects an array with the IDs as parameters.
[searchInCategories($_ids)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the IDs of the categories which are only to be searched through. Expects an array with the IDs as parameters.
[searchInCategoryTree($_id)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets an ID of a category as root search category. All searched articles need to be contained by it.
[searchInFileCategories($_ids)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the IDs of the mediapool-categories which are only to be searched through. Expects an array with the IDs as parameters.
[searchInDbColumn($_table, $_column)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the columns which only should be searched through.
[setSearchAllArticlesAnyway($_bool = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Selects if articles will be searched
[setWhere($_where)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Set additional WHERE Clause
[setOrder($_order, $put_first = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the sort order of the results.
[doGroupBy($_bool = true)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Use GROUP BY to group results by ftable,fid,clang
-----|-----
[setLogicalMode($_logicalMode)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the mode of how the keywords are logical connected.
[setTextMode($_textMode)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the mode concerning which text is to be searched through.
[setSearchMode($_searchMode)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the MySQL search mode.
[addWhitelist($_whitelist)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | This method adds weight to special words.
-----|-----
[setSurroundTags($_tags, $_endtag = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the surround-tags for found keywords. Expects either the start- and the end-tag or an array with both tags.
[setLimit($_limit, $_countLimit = false)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the maximum count of results. Expects either the start- and the count-limit or an array with both limits or only the count-limit.
[setMaxTeaserChars($_count)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the maximum count of letters the teaser of a searched through text may have.
[setMaxHighlightedTextChars($_count)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the maximum count of letters around an found search term in the highlighted text.
[setHighlightType($_type)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets the type of the text with the highlighted keywords.
[setBlacklist($_words)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Sets words, which must not be found. Expects an array with the words as parameters.
-----|-----
[setCaseInsensitive($_ci = true)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Case sensitive or case insensitive?
[parseSearchString($_searchString)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Converts the search string to an array.
[getHighlightedText($_text)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | According to the highlight-type this method will return a string or an array. Found keywords will be highlighted with the surround-tags.
-----|-----
[search($_search)](https://github.com/FriendsOfREDAXO/search_it/tree/master/lib/search_it.php) | Executes the search.
