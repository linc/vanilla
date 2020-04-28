import { globalVariables } from "@library/styles/globalStyleVars";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { unit, colorOut, absolutePosition, negativeUnit, paddings } from "@library/styles/styleHelpers";
import { iconClasses } from "@library/icons/iconStyles";
import { percent, translateX } from "csx";
import { shadowHelper, shadowVariables } from "@library/styles/shadowHelpers";
import { relative } from "path";
import { clickableItemStates } from "@dashboard/compatibilityStyles/clickableItemHelpers";

export const newPostMenuVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const themeVars = variableFactory("newPostMenu");

    const position = themeVars("position", {
        bottom: 40,
        right: 24,
    });

    const item = themeVars("itemAction", {
        position: {
            top: 16,
            right: 6,
        },
    });

    const action = themeVars("action", {
        borderRadius: 21.5,
        padding: {
            right: 18,
            left: 14,
        },
        size: {
            height: 44,
        },
    });

    const toggle = themeVars("toggle", {
        size: 56,
        on: {
            rotation: `-315deg`,
        },
        position: {
            top: 24,
        },
    });

    const label = themeVars("label", {
        margin: {
            left: 10,
        },
    });

    const items = themeVars("items", {
        offset: 25,
    });

    return {
        position,
        item,
        action,
        toggle,
        label,
        items,
    };
});

export const newPostMenuClasses = useThemeCache(() => {
    const style = styleFactory("newPostMenu");
    const vars = newPostMenuVariables();
    const globalVars = globalVariables();

    const root = style({
        position: "fixed",
        bottom: unit(vars.position.bottom),
        right: unit(vars.position.right),
        width: unit(vars.toggle.size),
        height: unit(vars.toggle.size),
    });

    const isOpen = style("isOpen", {
        transform: `rotate(${vars.toggle.on.rotation})`,
    });

    const toggle = style("toggle", {
        display: "inline-flex",
        alignItems: "center",
        justifyItems: "center",
        borderRadius: "50%",
        ...shadowHelper().dropDown(),
        marginTop: unit(vars.toggle.position.top),
        height: unit(vars.toggle.size),
        width: unit(vars.toggle.size),
        backgroundColor: colorOut(globalVars.mainColors.primary),
        $nest: {
            [`& .${isOpen} .${iconClasses().newPostMenuIcon}`]: {
                transform: translateX(vars.toggle.on.rotation),
            },
        },
    });

    const label = style("label", {
        marginLeft: unit(vars.label.margin.left),
        display: "inline-block",
    });

    // For animation purposes;
    const itemsWrapper = style("itemsWrapper", {
        overflow: "hidden",
        position: "fixed",
        background: "pink",
        right: unit(vars.position.right),
        bottom: unit(vars.position.bottom + vars.toggle.size / 2),
        paddingBottom: unit(vars.toggle.size / 2 + shadowVariables().newPostButton.verticalOffset + vars.items.offset),
    });

    const items = style("items", {
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-end",
        justifyContent: "flex-end",
        background: "orange",
    });

    const item = style("item", {
        marginTop: unit(vars.item.position.top),
    });

    const action = style("action", {
        borderRadius: unit(vars.action.borderRadius),
        ...shadowHelper().dropDown(),
        height: unit(vars.action.size.height),
        backgroundColor: colorOut(globalVars.mainColors.bg),
        ...paddings(vars.action.padding),
        display: "inline-flex",
        alignItems: "center",
        ...clickableItemStates(),
    });

    const itemLabelAnimation = style("itemLabelAnimation", {});

    return {
        root,
        item,
        action,
        isOpen,
        toggle,
        label,
        items,
        itemsWrapper,
        itemLabelAnimation,
    };
});
