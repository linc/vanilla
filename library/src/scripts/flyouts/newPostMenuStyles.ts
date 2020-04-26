import { globalVariables } from "@library/styles/globalStyleVars";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { unit, colorOut, absolutePosition, negativeUnit, flexHelper } from "@library/styles/styleHelpers";
import { iconClasses } from "@library/icons/iconStyles";
import { scale, translateX } from "csx";
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
        open: {
            rotation: `-315deg`,
        },
    });

    const animation = themeVars("animation", {
        time: 200,
        twist: {
            deg: 135,
        },
        state: {
            // This if from the mouse/keyboard
            timing: "ease-out",
            time: 150,
            scale: 1.1,
        },
        closed: {
            scale: 1,
            shadow: {
                ...shadowVariables().newPostButton,
            },
        },
        open: {
            scale: 0.95,
            shadow: {
                horizontalOffset: 0,
                verticalOffset: 1,
                blur: 3,
                spread: 0,
                opacity: 1,
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
        height: unit(vars.toggle.size),
        width: unit(vars.toggle.size),
    });

    const domStates = style("domStates", {
        transition: `transform ${vars.animation.state.time}ms ${vars.animation.state.timing}`,
        $nest: {
            [`&:hover, &:focus, &.focus-visible, &:active`]: {
                transform: scale(vars.animation.state.scale), // This was attempted with React Spring, but the dom events are really flaky in React
            },
        },
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
    });

    const label = style("label", {});

    const menu = style("menu", {
        ...absolutePosition.bottomRight("100%"),
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-end",
        justifyContent: "flex-end",
    });

    const animationWrapper = style("animationWrapperIcon", {
        ...absolutePosition.fullSizeOfParent(),
        ...flexHelper().middle(),
        borderRadius: "50%",
    });

    return {
        root,
        item,
        action,
        isOpen,
        toggle,
        label,
        menu,
        domStates,
        animationWrapper,
    };
});
