double getPrice(def accessors, def doc, def decimals, def round, def multiplier) {
    for (accessor in accessors) {
        def key = accessor['key'];

        if (!doc.containsKey(key) || doc[key].empty) {
            continue;
        }

        def factor = accessor['factor'];
        def value = doc[key].value * factor;

        value = Math.round(value * decimals);
        value = (double) value / decimals;

        if (!round) {
            return (double) value;
        }

        value = Math.round(value * multiplier);

        value = (double) value / multiplier;

        return (double) value;
    }

    return 0;
}

return getPrice(params['accessors'], doc, params['decimals'], params['round'], params['multiplier']);
