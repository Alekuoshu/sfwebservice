{*
* @author Farmalisto <alejandro.villegas@farmalisto.com.co>
* @copyright  Farmalisto
* @license    commercial license see license.txt
*}
{foreach from=$messages_html item=message_html}
    <div class="bootstrap">
        <div class="module_confirmation conf confirm">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {$message_html}
        </div>
    </div>
{/foreach}
