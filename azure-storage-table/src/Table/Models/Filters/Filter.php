<?php

/**
 * LICENSE: The MIT License (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * https://github.com/azure/azure-storage-php/LICENSE
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Table\Models\Filters
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzureLegacy\Storage\Table\Models\Filters;

/**
 * Filter operations
 *
 * @category  Microsoft
 * @package   MicrosoftAzureLegacy\Storage\Table\Models\Filters
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */
class Filter
{
    /**
     * Apply and operation between two filters
     *
     * @param Filter $left  The left filter
     * @param Filter $right The right filter
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Filters\BinaryFilter
     */
    public static function applyAnd(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'and', $right);
        return $filter;
    }

    /**
     * Applies not operation on $operand
     *
     * @param Filter $operand The operand
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Filters\UnaryFilter
     */
    public static function applyNot(Filter $operand)
    {
        $filter = new UnaryFilter('not', $operand);
        return $filter;
    }

    /**
     * Apply or operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyOr(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'or', $right);
        return $filter;
    }

    /**
     * Apply eq operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyEq(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'eq', $right);
        return $filter;
    }

    /**
     * Apply ne operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyNe(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'ne', $right);
        return $filter;
    }

    /**
     * Apply ge operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyGe(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'ge', $right);
        return $filter;
    }

    /**
     * Apply gt operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyGt(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'gt', $right);
        return $filter;
    }

    /**
     * Apply lt operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyLt(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'lt', $right);
        return $filter;
    }

    /**
     * Apply le operation on the passed filers
     *
     * @param Filter $left  The left operand
     * @param Filter $right The right operand
     *
     * @return BinaryFilter
     */
    public static function applyLe(Filter $left, Filter $right)
    {
        $filter = new BinaryFilter($left, 'le', $right);
        return $filter;
    }

    /**
     * Apply constant filter on value.
     *
     * @param mixed  $value   The filter value
     * @param string $edmType The value EDM type.
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Filters\ConstantFilter
     */
    public static function applyConstant($value, $edmType = null)
    {
        $filter = new ConstantFilter($edmType, $value);
        return $filter;
    }

    /**
     * Apply propertyName filter on $value
     *
     * @param string $value The filter value
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Filters\PropertyNameFilter
     */
    public static function applyPropertyName($value)
    {
        $filter = new PropertyNameFilter($value);
        return $filter;
    }

    /**
     * Takes raw string filter
     *
     * @param string $value The raw string filter expression
     *
     * @return \MicrosoftAzureLegacy\Storage\Table\Models\Filters\QueryStringFilter
     */
    public static function applyQueryString($value)
    {
        $filter = new QueryStringFilter($value);
        return $filter;
    }
}
