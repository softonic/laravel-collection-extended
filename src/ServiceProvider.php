<?php

namespace Softonic\Laravel\Collection;

/*
 * Class CollectionMacroServiceProvider.
 *
 * @package Softonic\Laravel\Collection
 * @author  Jose Manuel Cardona <josemanuel.cardona@softonic.com>
 */

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->registerGroupByHierarchy();
        $this->registerMultipleOnly();
    }

    private function registerGroupByHierarchy()
    {
        if (!Collection::hasMacro('groupByHierarchy')) {
            /*
             * Group an item list by the giving hierarchy keys.
             *
             * It produces the same output than groupBy but allows nested groups.
             *
             * Example:
             *      input:
             *          [
             *              [
             *                  'id_author' => 'author1',
             *                  'id_post'   => 'post1',
             *                  ...
             *              ],
             *              [
             *                  'id_author' => 'author1',
             *                  'id_post'   => 'post2',
             *                  ...
             *              ],
             *              [
             *                  'id_author' => 'author2',
             *                  'id_post'   => 'post3',
             *                  ...
             *              ],
             *          ]
             *
             *      code: dd($posts->groupByHierarchy('id_author', 'id_post'));
             *
             *      output:
             *          [
             *              'author1' => [
             *                      'post1' => [...],
             *                      'post2' => [...],
             *              ],
             *             'author2' => [
             *                      'post3' => [...],
             *              ],
             *          ]
             */
            Collection::macro('groupByHierarchy', function (...$args) {
                $hierarchyKeys = (is_array($args[0])) ? collect($args[0]) : collect($args);

                $hierarchyData = collect();
                foreach ($this->items as $item) {
                    $hierarchyItemPointer = $hierarchyData;
                    foreach ($hierarchyKeys as $key) {
                        $value = data_get($item, $key);
                        if (!isset($value)) {
                            throw new \RuntimeException("Grouping error: '${key}' doesn't exist in collection");
                        }

                        if (!isset($hierarchyItemPointer[$value])) {
                            $hierarchyItemPointer[$value] = collect();
                        }
                        $hierarchyItemPointer = $hierarchyItemPointer[$value];
                    }
                    $hierarchyItemPointer[] = $item;
                };

                return $hierarchyData;
            });
        }
    }

    private function registerMultipleOnly()
    {
        if (!Collection::hasMacro('extract')) {
            /*
             * Extract the values corresponding to the given arguments.
             *
             * Use cases:
             * - When using one table:
             *   Ex. extract('id_post', 'id_author')
             * - When using the result of this method inside queries with multiple tables,
             *   you need to specify the table the fields belong to:
             *   Ex. extract(
             *         ['post.id_post => 'id_post'],
             *         ['post.id_author => 'id_author']
             *       )
             * You can also pass all the arguments inside an array, instead of using multiple arguments:
             *   Ex. extract(['id_post', 'id_author'])
             */
            Collection::macro('extract', function (...$args) {
                $keys = (count($args) == 1 && is_array($args[0]) && is_numeric(key($args[0])))
                    ? collect($args[0])
                    : collect($args);

                return collect($this->items)->map(function ($item) use ($keys) {
                    return $keys->mapWithKeys(function ($keyToMap) use ($item) {
                        list($keyMapped, $keyToMap) = is_array($keyToMap)
                            ? [key($keyToMap), current($keyToMap)]
                            : [$keyToMap, $keyToMap];

                        return [$keyMapped => Arr::get($item, $keyToMap)];
                    });
                });
            });
        }
    }
}
