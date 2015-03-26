/**
 * Created by Maxim Omelchenko on 23.02.2015 at 12:29.
 */

/**
 * Class to work with SamsonCMS inputs
 * @type {{handlers: Array, bind: Function, update: Function}}
 */
var SamsonCMS_Input = {

    // Array of Input objects
    handlers: [],

    /**
     * Function to bind inputs
     * @param handler Input handler
     * @param selector Input selector
     */
    bind: function(handler, selector){
        this.handlers.push({
            handler: handler,
            selector: selector
        });
    },

    /**
     * Function to update handlers in block or on page
     * @param block SJSElement to search inputs in
     */
    update:function(block)
    {
        var i;
        var input;

        // Iterate over inputs
        for (i in this.handlers){
            input = this.handlers[i];
            // For all elements found on page
            s(input.selector, block).each(function(elem){
                // Call handler
                input.handler(elem);
            });
        }
    }
};

// Update handlers for whole page
s('body').pageInit(function(body){
    SamsonCMS_Input.update(body);
});