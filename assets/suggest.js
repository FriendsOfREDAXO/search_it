document.addEventListener('DOMContentLoaded', function () {
    function suggest(input, options) {
        var cache = [],
            cacheSize = 0,
            results = document.createElement('ul'),
            timeout = null,
            prevLength = 0;

        results.className = options.resultsClass;
        document.body.appendChild(results);

        function getPosition(el) {
            var rect = el.getBoundingClientRect();
            return {
                top: rect.top + window.scrollY,
                left: rect.left + window.scrollX
            };
        }

        function updatePosition() {
            var pos = getPosition(input);
            results.style.top = pos.top + input.offsetHeight + 'px';
            results.style.left = pos.left + 'px';
        }

        function handleKeyup(e) {
            if (/27$|38$|40$/.test(e.keyCode) && results.style.display !== 'none' || /^13$|^9$/.test(e.keyCode) && getCurrentResult()) {
                e.preventDefault();
                e.stopPropagation();
                switch (e.keyCode) {
                    case 38:
                        selectPrevious();
                        break;
                    case 40:
                        selectNext();
                        break;
                    case 9:
                    case 13:
                        chooseResult();
                        break;
                    case 27:
                        results.style.display = 'none';
                        break;
                }
            } else {
                if (input.value.length !== prevLength) {
                    clearTimeout(timeout);
                    timeout = setTimeout(fetchSuggestions, options.delay);
                    prevLength = input.value.length;
                }
            }
        }

        function fetchSuggestions() {
            var query = input.value.trim();
            if (query.length >= options.minchars) {
                var cached = getCached(query);
                if (cached) {
                    displayResults(cached.items);
                } else {
                    fetch(options.source + '&q=' + encodeURIComponent(query))
                        .then(response => response.text())
                        .then(data => {
                            var items = parseResults(data, query);
                            displayResults(items);
                            addToCache(query, items, data.length);
                        });
                }
            } else {
                results.style.display = 'none';
            }
        }

        function getCached(query) {
            for (var i = 0; i < cache.length; i++) {
                if (cache[i].q === query) {
                    cache.unshift(cache.splice(i, 1)[0]);
                    return cache[0];
                }
            }
            return false;
        }

        function addToCache(query, items, size) {
            while (cache.length && cacheSize + size > options.maxCacheSize) {
                var cached = cache.pop();
                cacheSize -= cached.size;
            }
            cache.push({q: query, size: size, items: items});
            cacheSize += size;
        }

        function displayResults(items) {
            if (items.length) {
                results.innerHTML = '';
                for (var i = 0; i < items.length; i++) {
                    var li = document.createElement('li');
                    li.innerHTML = items[i];
                    li.addEventListener('mouseover', function () {
                        var children = results.children;
                        for (var j = 0; j < children.length; j++) {
                            children[j].classList.remove(options.selectClass);
                        }
                        this.classList.add(options.selectClass);
                    });
                    li.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        chooseResult();
                    });
                    results.appendChild(li);
                }
                results.style.display = 'block';
            } else {
                results.style.display = 'none';
            }
        }

        function parseResults(data, query) {
            var items = data.split(options.delimiter).map(item => item.trim()).filter(item => item);
            var regex = new RegExp(query, 'ig');
            return items.map(item => item.replace(regex, function (match) {
                return '<span class="' + options.matchClass + '">' + match + '</span>';
            }));
        }

        function getCurrentResult() {
            var selected = results.querySelector('.' + options.selectClass);
            return selected || false;
        }

        function chooseResult() {
            var currentResult = getCurrentResult();
            if (currentResult) {
                input.value = currentResult.textContent;
                results.style.display = 'none';
                if (options.onSelect) {
                    options.onSelect.apply(input, [currentResult.textContent]);
                }
            }
        }

        function selectNext() {
            var currentResult = getCurrentResult();
            if (currentResult) {
                var next = currentResult.nextElementSibling;
                currentResult.classList.remove(options.selectClass);
                if (next) {
                    next.classList.add(options.selectClass);
                } else {
                    results.firstElementChild.classList.add(options.selectClass);
                }
            } else {
                results.firstElementChild.classList.add(options.selectClass);
            }
        }

        function selectPrevious() {
            var currentResult = getCurrentResult();
            if (currentResult) {
                var prev = currentResult.previousElementSibling;
                currentResult.classList.remove(options.selectClass);
                if (prev) {
                    prev.classList.add(options.selectClass);
                } else {
                    results.lastElementChild.classList.add(options.selectClass);
                }
            } else {
                results.lastElementChild.classList.add(options.selectClass);
            }
        }

        input.addEventListener('keyup', handleKeyup);
        input.addEventListener('blur', function () {
            setTimeout(function () {
                results.style.display = 'none';
            }, 200);
        });
        window.addEventListener('resize', updatePosition);
        window.addEventListener('load', updatePosition);

        updatePosition();
    }

    function initSuggest() {
        var searchInput = document.querySelector(".search_it-form input[name=search]");

        if (searchInput) {
            suggest(searchInput, {
                source: 'index.php?rex-api-call=search_it_autocomplete&rnd=' + Math.random(),
                delay: 100,
                resultsClass: 'ac_results',
                selectClass: 'ac_over',
                matchClass: 'ac_match',
                minchars: 2,
                delimiter: '\n',
                onSelect: function (value) {
                    var searchForm = searchInput.closest('.search_it-form');
                    if (searchForm.classList.contains('search_it-form-autocomplete')) {
                        searchForm.submit();
                        return false;
                    }

                },
                maxCacheSize: 65536
            });
        }
    }

    initSuggest();
});
