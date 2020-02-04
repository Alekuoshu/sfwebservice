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
  {block name='customer_form_errors'}
    {include file='_partials/sf-errors.tpl' errors=$errors[0]}
  {/block}
  {* {block name='notifications'}
    {include file='_partials/notifications.tpl'}
  {/block} *}

  <script src="themes/dekora_theme4/assets/js/jquery-ui.min.js"></script>
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

<form action="{block name='customer_form_actionurl'}{$action}{/block}" id="customer-form" class="js-customer-form" method="post">
  <section>
    {block "form_fields"}
      {foreach from=$formFields item="field" name=customerForm}
      {assign var="counter" value=$smarty.foreach.customerForm.iteration}
      {* {assign var="ffield" value=$field.value} *}
      {* {$ffield|var_dump} *}
        {block "form_field"}
        {* {if $counter == 1 || $counter == 2 || $counter == 3 || $counter == 4 || $counter == 5 || $counter == 6 || $counter == 7 || $counter == 8} *}
          {form_field field=$field}
        {* {/if} *}
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
          {l s='Save' d='Shop.Theme.Actions'}
        </button>
      {/block}
      {if !{$customer.is_logged}}
        <div class="siaccount">
          <a href="iniciar-sesion?back=index.php" data-link-action="display-login-form">
            {l s='¿Ya tienes una cuenta? Inicie Aquí' d='Shop.Theme.Customeraccount'}
          </a>
        </div>
      {/if}
    </footer>
  {/block}

</form>
{/block}

{if {$customer.is_logged}}
  <script type="text/javascript">
  // checkear checkbox si esta logueado y deshabilita email input
    (function ($) {
        $(document).ready(function () {
            //console.log('testing...');
            $('input[name="email"]').attr('disabled','disabled');
            var target2 = $('.custom-checkbox input[type="checkbox"]');
            if(!target2.length) return;
            target2.prop("checked", true);

            var btnSubmit = $('button[type="submit"]');
            btnSubmit.on('click', function(){
              $('input[name="email"]').removeAttr('disabled');
              setTimeout(function(){ $('input[name="email"]').attr('disabled','disabled'); }, 500);
            });
        });

    })(jQuery);
  </script>
{/if}
