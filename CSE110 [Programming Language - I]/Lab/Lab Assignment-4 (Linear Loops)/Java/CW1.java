public class CW1 {
    public static void main(String[] args) {
    int a = 24;
    while (a >= -6) 
    {
        System.out.print(a);
        if (a > -6) 
        {
            System.out.print(", ");
        }
        a -= 6;
    }
    System.out.println();

    int b = -10;
    while (b <= 20) 
    {
        System.out.print(b);
        if (b < 20) 
        {
            System.out.print(", ");
        }
        b += 5;
    }
    System.out.println();
}
}