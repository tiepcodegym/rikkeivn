<style>
    .question{
        z-index: 999;
    }
    .tooltip-inner{
        white-space: inherit !important;
        max-width: 650px;;
        width: 100vw;
    }
    .tooltip-inner .qs-explain{
        text-align: left;
    }
    .tooltip-inner .qs-explain >li{
        list-style-type: none;
        display: flex;
    }
    .tooltip-inner .qs-explain >li:not(:last-child){
        margin-bottom: 5px;
    }
    .tooltip-inner .qs-explain >li .explain-star{
        flex: 0 0 80px;
        width: 80px;
        max-width: 80px;
        text-align: right;
        padding-right: 10px;
    }
    .tooltip-inner .qs-explain >li .explain-text{
        flex-grow: 1;
    }
</style>