# Klassen-Referenz

Methode | Erläuterung
-----|-----
[__construct($_clang = false, $_loadSettings = true, $_useStopwords = true)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L72) | Class constructor
[generateIndex()](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L364) | Generates the full index at once.
[indexArticle($_id,$_clang = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L422) | Indexes a certain article.
[unindexArticle($_id,$_clang = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L544) | Removes an article from the index.
[unindexURL($_id)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L576) | Removes an URL from the index.
[indexColumn($_table, $_column, $_idcol = false, $_id = false, $_start = false, $_count = false, $_where = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L569) | Indexes a certain column. Returns the number of the indexed rows or false.
[indexFile($_filename, $_doPlaintext = false, $_clang = false, $_fid = false, $_catid = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L734) | Indexes a certain file. Returns SEARCH_IT_FILE_GENERATED or an error code.
[indexURL($id, $article_id, $clang_id, $profile_id, $data_id)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L384) | Indexes a certain url from URL Addon.
[deleteIndex()](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L960) | Deletes the complete search index.
[deleteIndexForType($texttype)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1032) | Deletes the complete search index for a special text type.
[deleteCache($_indexIds = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1691) | Truncates the cache or deletes all data that are concerned with the given index-ids.
[deleteKeywords()](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1772) | Delete Keywords
-----|-----
[setSearchString($_searchString)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L860) | Set search string
[searchInArticles($_ids)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1041) | Sets the IDs of the articles which are only to be searched through. Expects an array with the IDs as parameters.
[searchInCategories($_ids)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1051) | Sets the IDs of the categories which are only to be searched through. Expects an array with the IDs as parameters.
[searchInCategoryTree($_id)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1051) | Sets an ID of a category as root search category. All searched articles need to be contained by it.
[searchInFileCategories($_ids)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1061) | Sets the IDs of the mediapool-categories which are only to be searched through. Expects an array with the IDs as parameters.
[searchInDbColumn($_table, $_column)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1072) | Sets the columns which only should be searched through.
[setSearchAllArticlesAnyway($_bool = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L238) | Selects if articles will be searched
[setWhere($_where)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1091) | Set additional WHERE Clause
[setOrder($_order, $put_first = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1346) | Sets the sort order of the results.
[doGroupBy($_bool = true)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L243) | Use GROUP BY to group results by ftable,fid,clang
-----|-----
[setLogicalMode($_logicalMode)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1107) | Sets the mode of how the keywords are logical connected.
[setTextMode($_textMode)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1143) | Sets the mode concerning which text is to be searched through.
[setSearchMode($_searchMode)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1184) | Sets the MySQL search mode.
[addWhitelist($_whitelist)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1334) | This method adds weight to special words.
-----|-----
[setSurroundTags($_tags, $_endtag = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L975) | Sets the surround-tags for found keywords. Expects either the start- and the end-tag or an array with both tags.
[setLimit($_limit, $_countLimit = false)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L997) | Sets the maximum count of results. Expects either the start- and the count-limit or an array with both limits or only the count-limit.
[setMaxTeaserChars($_count)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L348) | Sets the maximum count of letters the teaser of a searched through text may have.
[setMaxHighlightedTextChars($_count)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L358) | Sets the maximum count of letters around an found search term in the highlighted text.
[setHighlightType($_type)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1246) | Sets the type of the text with the highlighted keywords.
[setBlacklist($_words)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1014) | Sets words, which must not be found. Expects an array with the words as parameters.
-----|-----
[setCaseInsensitive($_ci = true)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1350) | Case sensitive or case insensitive?
[parseSearchString($_searchString)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1276) | Converts the search string to an array.
[getHighlightedText($_text)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1419) | According to the highlight-type this method will return a string or an array. Found keywords will be highlighted with the surround-tags.
-----|-----
[search($_search)](https://github.com/friendsofredaxo/search_it/blob/Doku/lib/search_it.php#L1785) | Executes the search.
