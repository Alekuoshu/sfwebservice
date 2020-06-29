{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='customer_form'}
    {if !empty($errors[0])}
        {block name='login_form_errors'}
            {include file='_partials/sf-errors.tpl' errors=$errors[0]}
        {/block}
    {/if}
    
    {if {!$customer.is_logged}}
        {block name='login_form_messages'}
            {include file='_partials/messages.tpl'}
        {/block}
    {/if}
    
    {block name='login_social'}
        <div class="text-center">
            {if {!$customer.is_logged}}
                <div class="text-register-after">
                    {l s='Registrarte con' d='Shop.Theme.Customeraccount'}
                </div>
            {/if}
            {hook h='displaySocialLogin'}
            <div class="text-register-before">
            {if {!$customer.is_logged}}
                <span>{l s='O' d='Shop.Theme.Customeraccount'}</span>
                <hr class="text-register-before-hr" />
                <div class="text-register-before-text">
                    {l s='Completa el formulario' d='Shop.Theme.Customeraccount'}
                </div>
            {else}
                <div class="text-register-before-text">
                    {l s='Edita tus datos' d='Shop.Theme.Customeraccount'}
                </div>
            {/if}
            </div>
        </div>
    {/block}
    <form action="{block name='customer_form_actionurl'}{$action}{/block}" id="customer-form" class="js-customer-form" method="post">
        <section class="fields-register">
            {block "form_fields"}
                {foreach from=$formFields item="field" name=customerForm}
                    {assign var="counter" value=$smarty.foreach.customerForm.iteration}
                    {*
                    {assign var="ffield" value=$field.value}
                    *}
                    {*
                    {$ffield|var_dump}
                    *}
                    {block "form_field"}
                    {*
                    {if $counter == 1 || $counter == 2 || $counter == 3 || $counter == 4 || $counter == 5 || $counter == 6 || $counter == 7 || $counter == 8}
                    *}
                        {if $field.name == "email"}
                            {assign var=newAvailableValues value=['placeholder' => 'Ejemplo: jgonzales89@gmail.com']}
                            {$field.availableValues = $newAvailableValues}
                        {/if}
                        {if $field.name == "birthday"}
                            {assign var=newAvailableValues value=['placeholder' => $field.availableValues.placeholder]}
                            {$field.availableValues = $newAvailableValues}
                        {/if}

                        {if $field.name == "phone"}
                            {assign var=newAvailableValues value=['placeholder' => 'Ej: xxx xxx xxx']}
                            {$field.availableValues = $newAvailableValues}
                        {/if}
                        {form_field field=$field}
                    {*
                    {/if}
                    *}
                    {/block}
                {/foreach}
                {$hook_create_account_form nofilter}
            {/block}
        </section>
        {block name='customer_form_footer'}
            <footer class="form-footer clearfix">
                <input type="hidden" name="submitCreate" value="1">
                {block "form_buttons"}
                    <button class="btn btn-primary form-control-submit float-xs-right" data-link-action="save-customer" type="submit">
                    {if {!$customer.is_logged}}
                        {l s='Únete a Abbot' d='Shop.Theme.Actions'}
                    {else}
                        {l s='Actualizar' d='Shop.Theme.Actions'}
                    {/if}
                    </button>
                {/block}
            </footer>
        {/block}
    </form>
{/block}
<script src="{$urls.theme_assets}js/jquery-ui.min.js"></script>
<script type="text/javascript">
    // activate datepicker
        (function ($) {
            $(document).ready(function () {
                var target = $('input[name="birthday"]');
                if(!target.length) return;
                target.attr('autocomplete', 'disabled');
                target.datepicker({
                  autoSize: true,
                  dateFormat: "dd-mm-yy",
                  changeMonth: true,
                  changeYear: true,
                  dayNamesMin: [ "Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa" ],
                  monthNamesShort: [ "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" ],
                  yearRange: "1930:-18",
                  maxDate: "-18y",
                  currentText: "Hoy",
                  gotoCurrent: true,
                  //showMonthAfterYear: true
                });
            });

        })(jQuery);
</script>
{if {$customer.is_logged}}
    <script type="text/javascript">
        // Checkear checkbox si esta logueado y deshabilita email input.
        (function ($) {
            $(document).ready(function () {
                $('input[name="email"]').attr('disabled','disabled');
                var target2 = $('.custom-checkbox input[type="checkbox"]');
                if(!target2.length) {
                    return;
                }
                target2.prop("checked", true);
                var btnSubmit = $('button[type="submit"]');
                btnSubmit.on('click', function() {
                  $('input[name="email"]').removeAttr('disabled');
                  setTimeout(function(){ $('input[name="email"]').attr('disabled','disabled'); }, 500);
                });
            });
        })(jQuery);
    </script>
{/if}