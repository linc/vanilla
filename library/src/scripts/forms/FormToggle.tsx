/**
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React, { useState } from "react";
import ScreenReaderContent from "@library/layout/ScreenReaderContent";
import { useUniqueID } from "@library/utility/idUtils";
import { formToggleClasses } from "@library/forms/FormToggle.styles";
import classNames from "classnames";
import LinkAsButton from "@library/routing/LinkAsButton";
import { ButtonTypes } from "@library/forms/buttonTypes";
import { InformationIcon } from "@library/icons/common";
import { t } from "@vanilla/i18n";
import { useFormGroup, useOptionalFormGroup } from "@dashboard/forms/DashboardFormGroupContext";
import { ToolTip } from "@library/toolTip/ToolTip";

interface IProps {
    enabled: boolean;
    onChange: (enabled: boolean) => void;
    className?: string;
    indeterminate?: boolean;
    id?: string;
    labelID?: string;
    accessibleLabel?: string;
    visibleLabel?: string;
    visibleLabelUrl?: string;
    slim?: boolean;
    disabled?: boolean;
    tooltip?: string;
    name?: string;
}

export function FormToggle(props: IProps) {
    const {
        enabled,
        onChange,
        className,
        indeterminate,
        accessibleLabel,
        visibleLabel,
        visibleLabelUrl,
        slim,
        disabled,
        tooltip,
        name,
        ...IDs
    } = props;
    const [isFocused, setIsFocused] = useState(false);
    const labelContext = useOptionalFormGroup();

    if (labelContext.labelID == null && IDs.labelID == null && accessibleLabel == null && visibleLabel == null) {
        throw new Error("Either a labelID or accessibleLabel must be passed to <FormToggle />");
    }

    const ownLabelID = useUniqueID("formToggleLabel");
    const ownID = useUniqueID("formToggle");
    const id = IDs.id ?? labelContext.inputID ?? ownID;
    const labelID = IDs.labelID ?? labelContext.labelID ?? ownLabelID;
    const classes = formToggleClasses(slim ? { formToggle: { options: { slim } } } : undefined);

    const WellContainer = visibleLabel ? "span" : "label";

    const well = (
        <WellContainer
            className={classNames(props.className, classes.root, {
                isOn: enabled,
                isIndeterminate: indeterminate,
                isFocused,
                isDisabled: disabled,
            })}
        >
            <ScreenReaderContent>
                {!visibleLabel && accessibleLabel && <span id={labelID}>{accessibleLabel}</span>}
                <input
                    name={name}
                    className="exclude-icheck"
                    onFocus={() => setIsFocused(true)}
                    onBlur={() => setIsFocused(false)}
                    disabled={disabled}
                    aria-disabled={disabled}
                    type="checkbox"
                    aria-labelledby={labelID}
                    id={id}
                    checked={enabled}
                    onChange={(e) => {
                        onChange(e.target.checked);
                    }}
                />
            </ScreenReaderContent>
            <div className={classes.well} />
            <div className={classes.slider} />
        </WellContainer>
    );

    let toggle = visibleLabel ? (
        <label className={classes.visibleLabelContainer}>
            {visibleLabel && (
                <label id={labelID} className={classes.visibleLabel}>
                    {visibleLabel}

                    {visibleLabelUrl && (
                        <LinkAsButton
                            buttonType={ButtonTypes.ICON_COMPACT}
                            to={visibleLabelUrl}
                            ariaLabel={t("More information")}
                        >
                            <InformationIcon />
                        </LinkAsButton>
                    )}
                </label>
            )}
            {well}
        </label>
    ) : (
        well
    );

    if (tooltip) {
        toggle = <ToolTip label={tooltip}>{toggle}</ToolTip>;
    }
    return toggle;
}
