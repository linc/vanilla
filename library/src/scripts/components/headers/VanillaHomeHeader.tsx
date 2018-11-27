/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { Devices, IDeviceProps } from "../DeviceChecker";
import { withDevice } from "../../contexts/DeviceContext";
import VanillaMobileHomeHeader from "@library/components/headers/pieces/VanillaMobileHomeHeader";
import VanillaHeader from "@library/components/headers/VanillaHeader";
import { IMobileDropDownProps } from "@library/components/headers/pieces/MobileDropDown";

interface IProps extends IDeviceProps, IMobileDropDownProps {}

/**
 * Implements Vanilla Header component. Note that this component uses a react portal.
 */
export class VanillaHomeHeader extends React.Component<IProps> {
    public render() {
        const isMobile = this.props.device === Devices.MOBILE;
        return isMobile ? <VanillaMobileHomeHeader /> : <VanillaHeader />;
    }
}

export default withDevice(VanillaHomeHeader);
