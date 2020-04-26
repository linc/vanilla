import { globalVariables } from "@library/styles/globalStyleVars";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { unit, colorOut, absolutePosition, negativeUnit, flexHelper } from "@library/styles/styleHelpers";
import { iconClasses } from "@library/icons/iconStyles";
import { translateX } from "csx";
import { shadowHelper, shadowVariables } from "@library/styles/shadowHelpers";

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
        },
        padding: {
            horizontal: 18,
        },
        border: {
            radius: globalVars.borderType.formElements.buttons.radius,
        },
    });

    const toggle = themeVars("toggle", {
        size: 56,
        borderRadius: "50%",
        on: {
            rotation: `-315deg`,
        },
    });

    const animation = themeVars("animation", {
        time: 200,
        twist: {
            deg: 135,
        },
        closed: {
            state: {
                scale: 1.1,
                horizontalOffset: 0,
                verticalOffset: 0,
                blur: 0,
                spread: 0,
                opacity: 1,
            },
        },
        open: {
            state: {
                ...shadowVariables().newPostButton,
                scale: 0.95,
            },
        },
    });

    return {
        position,
        item,
        toggle,
        animation,
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
    });

    const isOpen = style("isOpen", {});

    const item = style("item", {
        marginTop: unit(vars.item.position.top),
    });

    const action = style("action", {
        borderRadius: unit(vars.item.border.radius),
        backgroundColor: colorOut(globalVars.mainColors.bg),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        cursor: "pointer",
    });

    const toggle = style("toggle", {
        display: "flex",
        alignItems: "center",
        justifyItems: "center",
        borderRadius: "50%",
        height: unit(vars.toggle.size),
        width: unit(vars.toggle.size),
        backgroundColor: colorOut(globalVars.mainColors.primary),
        $nest: {
            [`& .${isOpen} .${iconClasses().newPostMenuIcon}`]: {
                transform: translateX(vars.toggle.on.rotation),
            },
        },
    });

    const label = style("label", {});

    const menu = style("menu", {
        ...absolutePosition.bottomRight("100%"),
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-end",
        justifyContent: "flex-end",
    });

    const animationWrapperIcon = style("animationWrapperIcon", {
        ...absolutePosition.fullSizeOfParent(),
        ...flexHelper().middle(),
    });

    return {
        root,
        item,
        action,
        isOpen,
        toggle,
        label,
        menu,
        animationWrapperIcon,
    };
});
