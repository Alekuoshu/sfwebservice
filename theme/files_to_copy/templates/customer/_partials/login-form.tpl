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
 <script type="text/javascript" src="{$urls.theme_assets}js/validate.js"></script>
{block name='login_form'}
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
    
    {*
    {block name='login_form_errors'}
        {include file='_partials/form-errors.tpl' errors=$errors['']}
    {/block}
    *}
    {block name='login_social'}
        <div class="text-center">
            <div class="text-login-after">
                {l s='Logueate con' d='Shop.Theme.Customeraccount'}
            </div>
            {hook h='displaySocialLogin'}
            <div class="text-login-before">
                {l s='O ingresa con tu usuario' d='Shop.Theme.Customeraccount'}
            </div>
        </div>
    {/block}
    <form id="login-form" action="{block name='login_form_actionurl'}{$action}{/block}" method="post">
        <section>
            {block name='login_form_fields'}
                {foreach from=$formFields item="field"}
                    {block name='form_field'}
                        {if $field.name == "email"}
                            {assign var=newAvailableValues value=['placeholder' => 'Ejemplo: jgonzales89@gmail.com']}
                            {$field.availableValues = $newAvailableValues}
                        {/if}
                        {form_field field=$field}
                    {/block}
                {/foreach}
            {/block}
        </section>
        <div class="forgot-password">
            <a href="{$urls.pages.password}" rel="nofollow">
                {l s='Forgot your password?' d='Shop.Theme.Customeraccount'}
            </a>
        </div>
        {block name='login_form_footer'}
            <footer class="form-footer text-sm-center clearfix">
                <input type="hidden" name="submitLogin" value="1">
                {block name='form_buttons'}
                    <button class="btn btn-primary" data-link-action="sign-in" type="submit" class="form-control-submit">
                        {l s='Iniciar compra segura' d='Shop.Theme.Actions'}<i class="shopping-cart zmdi zmdi-lock-outline"></i>
                    </button>
                {/block}
            </footer>
        {/block}
    </form>
    <div class="content-invited">
        <span>{l s='O' d='Shop.Theme.Customeraccount'}</span>
        <hr class="content-invited-hr" />
        <div class="content-invited-text">
            {l s='Continua tu compra sin registrarte, si cambias de opinión puedes hacerlo luego de la compra' d='Shop.Theme.Customeraccount'}
        </div>
        <div class="content-invited-link">
            <a class="btn btn-primary" href="{$link->getPageLink('order', true)|escape:'html'}" title="{l s='Continuar como invitado' d='Shop.Theme.Customeraccount'}" rel="nofollow">
                {l s='Continuar como invitado' d='Shop.Theme.Customeraccount'}<i class="shopping-cart zmdi zmdi-lock-outline"></i>
            </a>
        </div>
    </div>
{/block}