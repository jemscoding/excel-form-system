import type { HeaderProps } from './props/props';

export const Header = ({ icon, title, description }: HeaderProps) => {

    return (
        <div className={`flex items-center gap-4 mb-4`}>
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary dark:bg-primary-foreground dark:border-bg-white shadow-lg">
                <div className="px-3 lg:px-0 lg:h-6 lg:w-6 dark:text-white text-primary-foreground">
                    {icon}
                </div>
            </div>
            <div>
                <h1 className="text-[20px] -mb-2 md:text-2xl lg:text-2xl font-extrabold tracking-tight text-foreground gochi-hand-regular">
                    {title}
                </h1>
                <p className="text-sm text-muted-foreground mt-1">
                    {description}
                </p>
            </div>
        </div>
    );
}