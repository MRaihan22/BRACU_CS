import java.util.Scanner;

public class HW7 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Starting Number: ");
        int start = sc.nextInt();
        System.out.print("Ending Number: ");
        int end = sc.nextInt();
        System.out.print("Divisor: ");
        int divisor = sc.nextInt();
        System.out.println("Output: ");

        for (int num = start; num <= end; num++)
        {
            int temp = num;
            int prod = 1;
            while (temp != 0)
            {
                int digit = temp % 10;
                prod *= digit;
                temp /= 10;
            }
            if (prod % divisor == 0)
            {
                System.out.print(prod + " ");
            }
        }
    }
}
